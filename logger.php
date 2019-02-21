<?php
@session_start();

class Logger {
  
  function __construct() {
    $directory = "I:/xampp/logs/DiscordApp";
    $dateformat = date("d-m-Y");
    @mkdir("$directory/$dateformat");
    $this->mainlog = fopen("$directory/$dateformat/main.log", "a+");
    $this->loginlog = fopen("$directory/$dateformat/logins.log", "a+");
    $this->uploadlog = fopen("$directory/$dateformat/uploads.log", "a+");
  }
  
  function uuid() {
    return str_replace("+", "",str_replace("/","",str_replace("==", "", base64_encode(random_bytes(6)))));
  }
  
  function newUpload(string $message) {
    $uploads = $this->uploadlog;
    $timestamp = date("[H:i:s] ");
    return fwrite($uploads, "\n$timestamp: $message");
  }
                             
  function newLogin(string $username) {
    $logins = $this->loginlog;
    $timestamp = date("[H:i:s] ");
    $ip = $_SERVER['REMOTE_ADDR'];
    return fwrite($logins, "\n$timestamp: '$username' Has logged in from $ip");
  }
  
  function newlog(string $message) {
    $mainlogs = $this->mainlog;
    $timestamp = date("[H:i:s] ");
    return fwrite($mainlogs, "\n$timestamp: $message");
  }
}