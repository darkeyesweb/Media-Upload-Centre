<?php
@session_start();

include 'logger.php';
$l = new Logger();

if (!isset($_SESSION['username'])) {
    echo "<script>window.location.href = 'login.html';</script>";
}

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\CognitoIdentity\CognitoIdentityClient;

function stitchChunks() {
    $filename = $_FILES['file']['name'];
    $category = $_POST['category'];
    @mkdir("M:/Pisscord/$category/");
    for ($i = 0; $i < $_POST["dztotalchunkcount"]; $i++) {
        file_put_contents("M:/Pisscord/$category/$filename", file_get_contents("M:/Pisscord/chunks/$filename.$i"), FILE_APPEND);
        unlink("M:/Pisscord/chunks/$filename.$i");
    }

    sleep(1);
}

function formatSize(int $size) {
    if ($size >= 1000) { //KB 
        if ($size >= 1000000) { //MB
            if ($size >= 1000000000) { //GB
                return round($size / 1000000000, 1).
                " GB";
            } else {
                return round($size / 1000000, 1).
                " MB";
            }
        } else {
            return round($size / 1000, 1).
            " KB";
        }
    } else {
        return $size.
        " B";
    }
}

if (!empty($_FILES)) {
    $category = $_POST['category'];
    if ($category == "") {
        die("no category");
    }
    $f = $_FILES["file"];
    $p = $_POST;
    $filename = $_FILES['file']['name'];
    $un = $_SESSION['username'];
    @mkdir("M:/Pisscord/$category/");
    if (isset($_POST["dzchunkindex"])) {
        if ($_POST["dzchunkindex"] == "0") {
            $l->newUpload("$un has started uploading a new file: 
	Filename: ".$f["name"]."
    Chunks: ".$p["dztotalchunkcount"]."
    Title: ".$p["title"]."
    Description: ".$p["description"]."
    Uploader: $un 
    Category: ".$p["category"]);
        }
        $ci = $_POST["dzchunkindex"];
        move_uploaded_file($_FILES['file']['tmp_name'], "M:/Pisscord/chunks/$filename.$ci");
        $l->newUpload("$un has uploaded chunk $ci");
        if ($_POST["dzchunkindex"] == $_POST["dztotalchunkcount"]-1) {
            stitchChunks();
            $l->newUpload($un."'s upload has been stitched");
            $upload = tryUpload($filename);
             if ($upload === true) {
                 die("Item Uploaded");
             } else {
                 die($upload);
             }
        }
    } else {
        move_uploaded_file($_FILES['file']['tmp_name'], "M:/Pisscord/$category/$filename");
        $l->newUpload("$un has uploaded a new file: 
    Filename: ".$f["name"]."
    Filesize: ".$f["size"]."
    Title: ".$p["title"]."
    Description: ".$p["description"]."
    Uploader: $un
    Category: ".$p["category"]);
        $upload = tryUpload($filename);
        if ($upload === true) {
            die("Item Uploaded");
        } else {
            die($upload);
        }
            //print_r(defined_vars());
    }
}


function tryUpload($filename) {
    $channels = array();
    $channels["videos-f"] = "526156546955280405";
    $channels["videos-m"] = "526157752066637835";
    $channels["pictures-f"] = "526154418375229461";
    $channels["pictures-m"] = "526156200543518730";
    $channels["original-content-f"] = "542032832005144595";
    $channels["original-content-m"] = "542033071541714947";
    $title = urlencode($_POST["title"]);
    $bucket = 'pisscord';
    $category = $_POST['category'];
    $desc = urlencode($_POST["description"]);
    $channelid = $channels[$category];
    $username = $_SESSION['username'];
    $fileext = strtolower(array_reverse(explode(".", $filename))[0]);
    $fn = urlencode($filename);
    $fs = urlencode(formatSize(filesize("M:/Pisscord/$category/$filename")));
    $key = str_replace("+", "", str_replace("/", "", str_replace("==", "", base64_encode(random_bytes(6)))));
    $formatname ="$key.$fileext";

    $keyname = "$category/$formatname";

    $l->newlog(shell_exec("ffmpeg -i \"M:/Pisscord/$category/$filename\" -vf \"thumbnail\" -frames:v 120 \"M:/Pisscord/thumbnails/$key.png\""));
    $s3 = new S3Client([
        'version' => 'latest',
        'region' => 'us-east-1',
        'credentials' => [
            'key' => 'AKIAJLVVJTHBPZ7RWG2A',
            'secret' => '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',
        ],
        //'ssl.certificate_authority' => 'I:/xampp/php/cacert.pem',
        'scheme' => 'http'
    ]);
    try {
        $l = new Logger();
        $l->newUpload($username."'s file is now being uploaded to Amazon S3 with the key '$keyname'");
        // Upload data.
        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $keyname,
            'Body' => file_get_contents("M:/Pisscord/$category/$filename"),
            'ACL' => 'public-read',
            'Tagging' => "uploader=$username&title=$title"
        ]);

        $l->newUpload("Pinging the bot to create a message");
        if ($_POST["anon"] == "1") {
            $username = "Anonymous";
        } else {
        	$uid = $_SESSION["userID"];
            $username = urlencode("<@$uid>");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost:8788/?link=https://cdn.pisscord.org/$keyname&ch=$channelid&title=$title&un=$username&desc=$desc&fs=$fs&fn=$fn");
        curl_exec($ch);
        $l->newUpload($username."'s file has been uploaded to Amazon S3: ".json_encode($result));
        return true; 
    } catch (S3Exception $e) {
        $l->newUpload($username."'s file failed to upload to Amazon S3: ".$e->getMessage());
        return $e->getMessage().PHP_EOL;
    }
}