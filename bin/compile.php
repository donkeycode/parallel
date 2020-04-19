<?php

// The php.ini setting phar.readonly must be set to 0
$pharFile = 'parallel.phar';

// clean up
if (file_exists($pharFile)) {
    unlink($pharFile);
}

if (file_exists($pharFile . '.gz')) {
    unlink($pharFile . '.gz');
}

// create phar
$p = new Phar($pharFile);
$p->setSignatureAlgorithm(\Phar::SHA1);

// creating our library using whole directory  
$p->buildFromDirectory('.');
$p->addFile('index.php');

// pointing main file which requires all classes  
$p->setDefaultStub('index.php', '/index.php');
   
echo "$pharFile successfully created";
