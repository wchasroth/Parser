<?php
   // Parse county name, website, facebook, and email address from
   // MDP county party list at https://michigandems.com/county-chairs
   //

   declare(strict_types=1);

   namespace CharlesRothDotNet\Parser;

   use CharlesRothDotNet\Alfred\Str;

   require_once('../vendor/autoload.php');

   $state = 0;  // outside of county block

   while ( ($line = fgets(STDIN)) !== false) {
      if ($state == 0) {
         if (Str::contains($line, '<h2 class="elementor-heading-title elementor-size-default')) {
            if (Str::contains($line, " County<")) {
               $state == 1;  // started county block
               $line = Str::replaceAll($line, "<BR>", "");
               $name = Str::substringBefore   ($line, " County<");
               $name = Str::substringAfterLast($name, ">");
               $name = trim($name);
               echo "$name\n";
            }
         }
         $state = 0;
      }
   }

   //<h2 class="elementor-heading-title elementor-size-default"><a href="https://www.allegandems.com" target="_blank">Allegan County</a></h2>				</div>
