<?php
   // Parse county name, website, facebook, and email address from
   // MDP county party list at https://michigandems.com/county-chairs
   //

   declare(strict_types=1);

   namespace CharlesRothDotNet\Parser;

   use CharlesRothDotNet\Alfred\Str;

   require_once('../vendor/autoload.php');

   $categoryMatches = [" County<", " Club<", " Caucus<",
       " District 1<", " District 2<", " District 3<",  " District 4<",  " District 5<",  " District 6<", " District 7<",
       " District 8<", " District 9<", " District 10<", " District 11<", " District 12<", " District 13<"];
   $excludeMatches = [ "www.w3.org/2000/svg", "michigandems.com", "secure.actblue.com", "secure.ngpvan.com" ];
   $category   = "";
   $lastLink = "";
   $web1Found = false;
   while ( ($line = fgets(STDIN)) !== false) {
       // Club<    District N<   Caucus<    County<
      if (Str::contains($line, '<h2 class="elementor-heading-title elementor-size-default')) {
         if (Str::hasAnyOf($line, $categoryMatches)) {
            $line = Str::replaceAll($line, "<BR>", "");
            $line = Str::replaceAll($line, "<br>", "");
            $name = getSubstringBeforeMatch($line, $categoryMatches);
            $name = Str::substringAfterLast($name, ">");
            $category = trim($name);
            $category = Str::replaceAll($category, ".", "");
            $web1Found = false;
         }
      }

      if (! empty($category)  &&  ! Str::hasAnyOf($line, $excludeMatches)) {
          $link = extractHyperlink($line);
          if (! empty($link)  &&  ! similarUrls($link, $lastLink)) {
              if (Str::contains($line, "Chip in to elect"))  break;
              if ($category == "Wexford"  &&  Str::contains($link, "secure.actblue"))  break;  // DONE!

              $column = getColumnFor($link);
              if ($column == "web") {
                  $column = ($web1Found ? "web2" : "web1");
                  $web1Found = true;
              }
//              $sql = generateUpdateSql($category, $column, $link);
              echo "$category  $column  $link\n";
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

   function getSubstringBeforeMatch(string $line, array $categories): string {
       foreach ($categories as $category) {
           if (Str::contains($line, $category)) {
               $name = Str::substringBefore($line, $category);
               if (!empty($name)) return $name;
           }
       }
       return "";
   }
