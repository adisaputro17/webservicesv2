<?php

require_once "constants.php";

function uploadSKP($fupload_name){
  //direktori file
  $vdir_upload = "../$folder_files_skp/";
  $vfile_upload = $vdir_upload . $fupload_name;

  //Simpan file
  move_uploaded_file($_FILES["fskp"]["tmp_name"], $vfile_upload);
}

?>