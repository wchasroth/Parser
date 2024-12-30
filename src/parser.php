<?php
   // Parse county name, website, facebook, and email address from
   // MDP county party list at https://michigandems.com/county-chairs
   //

   declare(strict_types=1);

   namespace CharlesRothDotNet\Parser;

   use CharlesRothDotNet\Alfred\Str;

   require_once('../vendor/autoload.php');

   $county   = "";
   $lastLink = "";
   $web1Found = false;
   while ( ($line = fgets(STDIN)) !== false) {
      if (Str::contains($line, '<h2 class="elementor-heading-title elementor-size-default')) {
         if (Str::contains($line, " County<")) {
            $line = Str::replaceAll($line, "<BR>", "");
            $line = Str::replaceAll($line, "<br>", "");
            $name = Str::substringBefore   ($line, " County<");
            $name = Str::substringAfterLast($name, ">");
            $county = trim($name);
            $county = Str::replaceAll($county, ".", "");
            $web1Found = false;
         }
      }

      if (! empty($county)) {
          $link = extractHyperlink($line);
          if (! empty($link)  &&  ! similarUrls($link, $lastLink)) {
              if ($county == "Wexford"  &&  Str::contains($link, "secure.actblue"))  break;  // DONE!

              $column = getColumnFor($link);
              if ($column == "web") {
                  $column = ($web1Found ? "web2" : "web1");
                  $web1Found = true;
              }
              $sql = generateUpdateSql($county, $column, $link);
              echo "$sql\n";
              $lastLink = $link;
          }
      }
   }

   function generateUpdateSql (string $county, string $column, string $link): string {
       return "UPDATE county_contacts SET $column = '$link' WHERE id = (SELECT id FROM county WHERE name='$county');";
   }

   function getColumnFor (string $link): string {
       $column = "email";
       if (Str::contains($link, "http")) {
           if      (Str::contains($link, "facebook"))  $column = "facebook";
           else if (Str::contains($link, "twitter"))   $column = "twitter";
           else if (Str::contains($link, "instagram")) $column = "instagram";
           else                                        $column = "web";
       }
       return $column;
   }

   function extractHyperlink(string $line): string {
      if      (Str::contains($line, "http://"))   $protocol = "http://";
      else if (Str::contains($line, "https://"))  $protocol = "https://";
      else if (Str::contains($line, "mailto:"))   $protocol = "mailto:";
      else return "";

      $text = Str::substringAfter ($line, $protocol);
      $text = Str::substringBefore($text, '"');
      $text = rtrim($text, "/");
      $text = Str::replaceAll($text, "%20", "");
      if (Str::contains($text, "www.w3.org/2000/svg"))   return "";
      if (Str::contains($text, ".pdf"))                  return "";
      if (Str::contains($protocol, "http")  &&  Str::contains($text, "@gmail")) {
          fwrite(STDERR, "Error: $protocol $text\n");
          return "";
      }
      return "$protocol$text";
   }

   function similarUrls(string $url1, string $url2): bool {
       $url1 = simplifyUrl($url1);
       $url2 = simplifyUrl($url2);
       return $url1 == $url2;
   }

   function simplifyUrl(string $url): string {
       $url = Str::replaceAll(strtolower($url), "https:", "http:");
       return Str::replaceAll(($url), "www.", "");
   }
