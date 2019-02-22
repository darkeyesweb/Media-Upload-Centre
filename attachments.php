<?php
@session_start();

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;


if (!empty($_GET)) {
	if (isset($_GET ['fn'])) {
		$bucket = 'pisscord';
    $desc = urldecode(_GET["desc"]);
    $channelid = $_GET['ch'];
		$uid = $_GET['uid'];
    $username = urlencode("<@$uid>");
		$filename = urldecode($_GET['fn']);
    $fileext = strtolower(array_reverse(explode(".", $filename))[0]);
    $fn = urlencode($filename);
    $fs = urlencode(formatSize(filesize("M:/Pisscord/attachments/$filename")));
    $key = str_replace("+", "", str_replace("/", "", str_replace("==", "", base64_encode(random_bytes(6)))));
    $formatname ="$key.$fileext";

    $keyname = "attachments/$formatname";

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
        // Upload data.
        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $keyname,
            'Body' => file_get_contents("M:/Pisscord/attachments/$filename"),
            'ACL' => 'public-read',
            'Tagging' => "uploader=$username&title=Attachment"
        ]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost:8788/?link=https://cdn.pisscord.org/$keyname&ch=$channelid&title=Attachment&un=$username&desc=$desc&fs=$fs&fn=$fn");
        curl_exec($ch);
    } catch (S3Exception $e) {
        $l->newUpload($username."'s file failed to upload to Amazon S3: ".$e->getMessage());
        return $e->getMessage().PHP_EOL;
    }
	}
}