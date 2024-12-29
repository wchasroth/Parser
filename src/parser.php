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
   while ( ($line = fgets(STDIN)) !== false) {
      if (Str::contains($line, '<h2 class="elementor-heading-title elementor-size-default')) {
         if (Str::contains($line, " County<")) {
            $line = Str::replaceAll($line, "<BR>", "");
            $name = Str::substringBefore   ($line, " County<");
            $name = Str::substringAfterLast($name, ">");
            $county = trim($name);
         }
      }

      if (! empty($county)) {
          $link = extractHyperlink($line);
          if (! empty($link)  &&  ! similarUrls($link, $lastLink)) {
              if ($county == "Wexford"  &&  Str::contains($link, "secure.actblue"))  break;  // DONE!
              echo "$county  $link\n";
              $lastLink = $link;
          }
      }
   }

   function extractHyperlink(string $line): string {
      if      (Str::contains($line,"http://"))   $protocol = "http://";
      else if (Str::contains($line,"https://"))  $protocol = "https://";
      else return "";

      $text = Str::substringAfter ($line, $protocol);
      $text = Str::substringBefore($text, '"');
      $text = rtrim($text, "/");
      if (Str::contains($text, "www.w3.org/2000/svg"))   return "";
      if (Str::contains($text, ".pdf"))                  return "";
      if (Str::contains($text, "@gmail"))                return "";
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
