<?php
   // Mostly-empty template for a generic parsing program.
   // E.g. it reads from stdin, does stuff, and writes to stdout.
   //
   // (Using composer to get whatever packages it needs -- in this
   // case, using functions from the Alfred/Str class.)

   declare(strict_types=1);

   namespace CharlesRothDotNet\Parser;

   use CharlesRothDotNet\Alfred\Str;

   require_once('../vendor/autoload.php');

   while ( ($line = fgets(STDIN)) !== false) {
      echo Str::contains($line, "<h2") ? "H " : "  ";
      echo $line;
   }
   
