<?php

function uploadSKP($folder_files_skp, $fupload_name)
{
    //direktori file
    $vdir_upload = "../$folder_files_skp/";
    $vfile_upload = $vdir_upload . $fupload_name;

    //Simpan file
    move_uploaded_file($_FILES["fskp"]["tmp_name"], $vfile_upload);
}

function logAPI($koneksi, $nama_api, $request, $status, $error_message)
{
    $request_json = json_encode($request);

    $sql = "INSERT INTO api_logs (nama_api, request, status, error_message) VALUES ('$nama_api', '$request_json', '$status', '$error_message')";

    mysqli_query($koneksi, $sql);
}

?>