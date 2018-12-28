<?php


$filePaths = explode(
    ',',
    "\\\cncltd\cnc\import_attachment\live\New User Account Details Form - Laura Davey_20181227140500.docx"
);

echo get_current_user();

foreach ($filePaths as $filePath) {

    if (!file_exists($filePath)) {
        echo 'file does not exist??';
    }

    if ($handle = fopen(
        $filePath,
        'r'
    )) {

        if ($finfo = finfo_open(FILEINFO_MIME_TYPE)) {
            $attachmentMimeType = finfo_file(
                $finfo,
                $filePath
            );
        } else {
            $attachmentMimeType = '';   // failed to locate magic file for MimeTypes
        }

        echo 'opened file no problems: ' . $filePath;
    } else {
        echo 'Could not open file: ' . $filePath;
    }
}