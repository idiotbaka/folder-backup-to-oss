### 介绍
PHP 实现的自动每日压缩备份文件夹至 oss，支持 Windows 和 linux。

### 使用方式（Windows）

1. 下载仓库代码
2. 配置 `config.json`
3. 无需自己安装 PHP 环境，双击 `run_windows.bat` ，并保持窗口运行

### 使用方式（Linux）

1. 下载仓库代码 `git clone https://github.com/idiotbaka/folder-backup-to-oss` 并进入目录

2. 配置 `config.json`

3. 安装软件包 `php` 和 `zip`，例如在 ubuntu 中可以使用`apt install php zip` 来安装

4. `php run_linux.php`保持运行。可以使用 `screen`等工具让程序在后台运行

   （也可以修改代码，改为`crontab`定时运行方式）

### config.json 说明

| key                    | 描述                                                         | 示例                                                         |
| ---------------------- | ------------------------------------------------------------ | ------------------------------------------------------------ |
| backup_folder_path     | 要压缩备份的目录地址                                         | 【win】D:\\\\folder1\\\\need-backup<br />【linux】/home/ubuntu/folder/need-backup |
| access_key_id          | OSS 的 AccessKeyId                                           | 在 https://usercenter.console.aliyun.com/#/manage/ak 查询    |
| access_key_secret      | OSS 的 AccessKeySecret                                       | 在 https://usercenter.console.aliyun.com/#/manage/ak 查询    |
| endpoint               | OSS 的 bucket 所在 endpoint                                  | http://oss-cn-beijing.aliyuncs.com                           |
| bucket                 | OSS的 bucket 名称                                            | backup-bucket，需要提前创建好                                |
| bukket_path            | 要存储在 bucket 的哪个路径，存储在根目录则不填               | backup-folder                                                |
| upload_filename_prefix | 存储到 bucket 的文件前缀，格式为  `前缀-日期.zip`，例如  `backup-2021-07-18_23_43_03.zip` | backup                                                       |
| backup_time            | 运行备份的时间（小时：分钟），例如每天3点运行则是`03:00`     | 03:00                                                        |
| auto_delete_local      | 是否在上传 OSS 完成后自动删除本地压缩文件，填`true` 或 `false` | true                                                         |

