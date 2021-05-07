<?php
include_once dirname(__DIR__) . '/vendor/autoload.php';

use Janfish\Upload\File as File;

$chunkQuantity = $_POST['chunkQuantity'];
$currentChunkNo = $_POST['chunkNo'];
$fileName = $_FILES['file']['name'];
$tempPath = $_FILES['file']['tmp_name'];
$all = pathinfo($fileName);
$extension = $all['extension'];
$name = $all['filename'];
$dist = 'files/' . $name . '.' . $extension;

$file = new File($chunkQuantity);
$file->setSessionKey($fileName);
if (!$file->append($currentChunkNo, $tempPath, $dist)) {
    echo json_encode(['status' => false, 'isFinished' => false]);
} else {
    echo json_encode(['status' => true, 'isFinished' => $file->isFinished()]);
}


