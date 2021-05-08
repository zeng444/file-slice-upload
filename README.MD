#### 文件分片上传

> 文件分片，实现超大文件的在线上传

```
$chunkQuantity = $_POST['chunkQuantity'];
$currentChunkNo = $_POST['chunkNo'];
$fileName = $_FILES['file']['name'];
$tempPath = $_FILES['file']['tmp_name'];
$all = pathinfo($fileName);
$extension = $all['extension'];
$name = $all['filename'];
$dist = 'files/' . $name . '.' . $extension;

$file = new Janfish\Upload\File($chunkQuantity);
$file->setSessionKey($fileName);
if (!$file->append($currentChunkNo, $tempPath, $dist)) {
    die(json_encode([
        'status'=>'success',
        'data' => [
            'path' => $dist,
            'url' => '',
            'isFinished' => false,
        ],
    ]))
}
die(json_encode([
    'status'=>'success',
    'data' => [
        'path' => $dist,
        'url' => '',
        'isFinished' =>  $file->isFinished(),
    ],
]))

```


### html Vue组件

> 基于elementUi

```
<slice-upload
    v-model="video"
    btn-text="上传视频"
    api="/resources/video"
    accept="application/x-msdownload;application/pdf"
    :show-progress="true"
    chunk-size="1"
    @on-progress="progressHandle"
    @on-error="errorHandle" />
```

- 参数

| 项 | 类型 |默认值 | 说明 |
|-----|------|------|-------|
| btn-text | string |上传文件  | 上传按钮显示 |
| api | string |-  | 上传后端接口Url |
| show-progress | boolean | true  | 是否显示进度条 |
| chunk-size | number | 1  | 单个分片大小，单位M |
| accept | string | -  | 允许的文件格式，默认为支持所有 |

- 事件

| 项  | 类型 | 说明 |
|-----|------|------|
| on-progress | function(rate) |  上传进度   |
| on-error   | function(msg) |  上传发生错误的回调事件    |
| on-success  | function(fileUrl, filePath) |  上传成功的回调事件    |