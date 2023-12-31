<?php 
function removeFolder($path) {
    // process of deleting the folder in audio files
    if (is_dir($path)) {
        $iterator = new DirectoryIterator($path);
    
        
        // loop through each content inside the folder
        foreach($iterator as $file) {
            if ($file->isFile()) {
                unlink($file->getRealPath()); // get the relative path and delete the file
            } elseif (!$file->isDot() && $file->isDir()) {
                rmdir($file->getRealPath()); // get the relative path and delete the folder
            }
        }
        
        // finally, delete the now-empty folder
        rmdir($path);
    }
}

function fetchAudioContent($fileid) {
    global $dbcon;
    $datequery = "SELECT file_name, DATE_FORMAT(upload_date, '%m%d%Y_%H%i%s') AS formatted_date 
                        FROM audio_files WHERE file_id = '$fileid'";
    $dateresult = mysqli_query($dbcon, $datequery);

    $audiofile =  mysqli_fetch_assoc($dateresult);

    // get the filename and extension
    $filename = pathinfo($audiofile['file_name'], PATHINFO_FILENAME);
    $extension = pathinfo($audiofile['file_name'], PATHINFO_EXTENSION);

    // put filename, extension and date in array, these infos are needed to select
    //  specific record from the database in order to delete a specific file
    $info = array("filename" => $filename, 
                "extension" => $extension,
                "formatted_date" => $audiofile['formatted_date']);

    return $info;
}

function deleteAudioFile($fileid, $userid) {
    //get the audio file content from the database, to be used on locating the filename in audio_files folder
    $audiofile = fetchAudioContent($fileid);

    // remove the audio file uploaded by user
    $filenameMask = "audio_files/" . $userid . "_" . $audiofile['filename'] . $audiofile['formatted_date'] . "." . $audiofile['extension']; 
    if (file_exists($filenameMask)) 
        unlink($filenameMask);
    
    // remove the folder created on specific file and all its contents
    $folderMask = "audio_files/" . $userid . "_" . $audiofile['filename'] . $audiofile['formatted_date'];
    removeFolder($folderMask);
}

function deleteAllAudioFiles($userid) {
    // since we delete all, get all the files and folder that has the user_id in file
    $mask = "audio_files/" . $userid . "_*";

    // put all the folder/filenames that matches the $mask in an array
    $items = glob($mask);

    
    foreach ($items as $file) {
    
        // check if it's an audio file or a folder, then delete them 
        //  using their designated step
        if (is_file($file)) {
            unlink($file);
        } elseif (is_dir($file)) {
            removeFolder($file);
        }
    
    }
}

?>