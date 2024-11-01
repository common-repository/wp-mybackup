=== Plugin Name ===
Contributors: eugenmihailescu
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QRVSVFT9AUL7S
Tags:
backup,restore,schedule,cron,cloud,database,mysql,mysqldump,disk,file-system,local,upload,download,encryption,lzf,zip,openssl,mcrypt,dropbox,google,ftp,scp,sftp,ssh,webdav,mysql,email,incremental,differential,full,database
backup,db backups,dropbox backup,dump,file,ftp backup,ftps,full backup,google
cloud storage,google cloud backup,migrate,multisite,mysql
backup,restoration,scp backup,sftp backup,sicherung,webdav backup,website
backup,wordpress backup,upload,e-mail,email,mail,email backup,automatic
backup,encrypted backup,bz2,gz,zip,tar,lzf,pclzip
Requires at least: 3.0
Tested up to: 4.8
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Creates, restores, encrypts and schedules your backups (full, incremental, differential) to disk, Dropbox, Google Drive,FTP(s),SCP,SFTP,WebDAV,e-mail

== Description ==

[WP MyBackup](http://mynixworld.info/shop) is a multisite plugin that enables blog authors and system administrators to backup and restore their blog and/or system files with ease. It allows you to create a full, differential or incremental backups of both files and your MySQL databases. Furthermore it secures the backup by uploading it to the local disk, Ftp(s), Scp, SFtp, Dropbox, Google Drive, Webdav or sent via SMTP within a single/multiple e-mail messages as attachments.

The backup may be stored uncompressed (as a TAR archive) or compressed as TAR/Zip archives using GZip/BZip/LZF respectively Zip compression. Nonetheless the archive may be encrypted on the fly with an AES (Rijndael) cipher using a 128/192/256 bit key such that its content is protected from the curious eyes (it will take billions years to crack a 256-bit key).

It allows you to restore with ease any backup created by itself or by some other application/plug-in both via an enhanced Restore Wizard (Pro) addon and/or a (free) built-in quick restore feature.  

In order to help you understand how it works and/or diagnose a particular issue it includes enhanced debugging functionalities. The backup and restore jobs, the HTTP communication, the PHP and Ajax calls as well as well as the SMTP and SQL statements, all are logged into separated detailed log files.

https://www.youtube.com/watch?v=CmOLBfBRnrE

= Major features included(*) in WP MyBackup = 

* Support for WordPress multisite/network
* Support for open_basedir, safe_mode, disable_functions, max_execution_time, memory_limit PHP directives
* Support for websites hosted by `free web hosting` providers (where `open_basedir` and `safe_mode` are in effect)
* Support for creating full (complete), incremental and differential backups
* Support for splitting a large backup into multiple archive volumes
* Allows you to select **what** components (such as plugins, themes, WP core, etc) to include, **how** to store and **where** to store the backups
* Allows a complete backup of the system (not only your WordPress files) by giving you access to the whole file system
* Comes with support for backing up any remote MySQL database
* Additionally, allows MySQL backups via the local mysqldump toolchain including custom options support
* Allows the usage of OS compression toolchain (additionally to its default compression toolchain)
* Offers Zip archive support for maximum portability and LZF compression for maximim speed
* Encrypts/decrypts the backup archives using the AES (Rijndael) cipher with a 128/192/256 bit key
* Allows backup execution from command line via a complete CLI interface
* Support for restoring a full, increment or differential backup set created by itself or by some other application/plug-in
* Allows definition of multiple backup and restore jobs via an user-friendly Wizard
* Allows backup schedule at WordPress level and at the OS level where the backup job is run via the CLI interface
* WP MyBackup is known to work on Firefox, Chrome, IE, Opera over IIS/Apache with MySQL v5.0+, PHP 5.3+ and WordPress 3.0+

> **Premium Support**
>
> The users of the free version hosted by wordpress.org are welcome to use our free online support resources such as [guides](http://mynixworld.info/shop/getting-started-with-mybackup/), [tutorials](http://mynixworld.info/shop/tutorials), [FAQ](http://mynixworld.info/shop/faq-mybackup), [Knowledge Base](http://mynixworld.info/shop/knowledge-base) and [YouTube channel](#) videos. Read more [here](http://mynixworld.info/shop/get-support/).
>
> However, if you need dedicated one-time assistance regarding installation, job definitions, creation or restoration of a backup copy, or if you just need ongoing support, we are here to help you. More about this [here](http://mynixworld.info/shop/shop/premium-support).


= Other features you will love =

* Allows saving the CPU and networking bandwidth during the backup execution by limiting (throttling) the usage of these resources
* Comes with an enhanced backup history integrated with statistics and charting
* Allows tweaking the networking settings (like proxy, SSL, throttling, network interface, timeout, etc)
* Comes with file explorer support to allow you access any file from the local/remote storage (like local disk, Dropbox, Google, FTP, SSH, WebDav, etc)
* The file explorer allows direct operations on the local and cloud storages such as direct downloads, delete, rename or directory creation
* Keeps the track of what is doing in separate debug log files: backup/restore jobs, HTTP communication, PHP errors/back-traces and Ajax calls, SMTP communication, SQL statements, SQL restore, etc.
* Automatic log archiving and rotation
* Responsive layout (mobile devices friendly)


> <a name="key_note"></a>(*) Please note that some of these features are not included in the free version. They may be bought and installed separately. See [here](http://mynixworld.info/shop/comparison) a comparison between the free and Pro versions. Check also the [full feature list](http://mynixworld.info/shop/full-features-list/) or the [60+ screenshots gallery](http://mynixworld.info/shop/screenshots/) that reveals the most of these features at work. You can even test a live demo on our [MyBackup Sandbox](http://sandbox.mynixworld.info/mybackup/).

= WP MyBackup Pro =

This plugin comes in two different flavors:

* WP MyBackup Lite - the free edition of this plugin hosted at WordPress.org. This edition should be just fine for the average blogger.
* [WP MyBackup Pro](http://mynixworld.info/shop/product/wp-mybackup-pro/) - the premium edition which is hosted by [ourselves](http://mynixworld.info/shop/). It is oriented towards those users who need a more robust and customizable backup application. It takes the advantage of over [20+ add-ons](http://mynixworld.info/shop/product-category/addons/) that enhance the free version in all its aspects, from performance to functionality. For a comparison between the two editions please see a [features comparison matrix](http://mynixworld.info/shop/comparison/).

= Localization =

* English (default) - always included
* .pot file (`default.po`) for translators is also included
* *Want to contribute with your language? [Translations are welcome](http://mynixworld.info/shop/localization/)*

= Feedback =

* I am open for your suggestions and feedback - Thank you for using or trying out one of my plugins!
* Drop me a line [@eugenmihailescu](http://twitter.com/eugenmihailescu) on Twitter
* Follow me on [my Facebook page](http://www.facebook.com/eugenmihailescu)
* Or follow me on [+Eugen Mihailescu](http://plus.google.com/+EugenMihailescu) on Google Plus ;-)

== Installation ==

[Please read our complete installation tutorial](http://mynixworld.info/shop/tutorials/#wp_install).

== Frequently Asked Questions ==

The answers to the most frequently asked questions can be found at [MyBackup FAQ page](http://mynixworld.info/shop/faq-mybackup).

== Screenshots ==

1. The dashboard concentrates the most used options in only one place
2. The backup job at work showing the current execution status and progress
3. The backup job general settings. But there are more...
4. What WordPress components to include within the backup (plugins,themes,content,etc)
5. File backup exclusion filters: by directories, by extension, by pattern, etc
6. What MySQL database|tables to include within the backup (regex pattern allowed)
7. Where to upload the backup archives: disk, ftp, ssh, dropbox, google, webdav, e-mail
8. Set an automatic backup scheduler with a recurrence and start date-time
9. When something doesn't work as expected the log files are your friend...
10. An example of a log file: here the CURL log file
11. The support's ground zero point: debug an trace settings...
12. Upload your external (ie. custom) backup archives and restore your WP website
13. Restore your WordPress website from a previous backup copy
14. The restore job at work showing the current execution status and progress
15. The log of a backup/restore job sent automatically by email 

== Changelog ==

Please visit [MyBackup blog](http://mynixworld.info/shop/blog) for a more detailed version of changelog.

<div class='postbox'><h3>Version Change Log</h3><div class='inside'>
<span>1.0-3</span><ul><li><strong>[new]</strong> added WP action hooks on before|after job start (<a href="http://mynixworld.info/shop/faq-mybackup/#q24">API</a>)</li>
<li><strong>[new]</strong> added WP filter to return running schedule job interval name (<a href="http://mynixworld.info/shop/faq-mybackup/#q26">API</a>)</li></ul>
<span>1.0-2</span><ul><li><strong>[fix]</strong> fixed CPU throttle on safe_mode or `sleep` function restricted PHP environments</li></ul>
<span>1.0-1</span><ul><li><strong>[improvement]</strong> highlight and order by the enabled backup targets tabs</li>
<li><strong>[improvement]</strong> responsive layout (mobile devices friendly)</li>
<li><strong>[fix]</strong> fixed the fake notice `Download in your browser is troublesome`   </li></ul>
<span>0.2.3-37</span><ul><li><strong>[update]</strong> WordPress 4.7 compatible</li></ul>
<span>0.2.3-35</span><ul><li><strong>[improvement]</strong> set email priority by backup/restore status</li>
</ul>
<span>0.2.3-34</span><ul><li><strong>[new]</strong> added Restore debug log option (see Support/Log tab)</li></ul>
<span>0.2.3-33</span><ul><li><strong>[fix]</strong> completing backup in case of file access error</li>
<li><strong>[fix]</strong> admin screen flickering due to .htaccess on Apache 2.4+</li>
<li><strong>[fix]</strong> erroneous alert message about deleting .lock file</li>
<li><strong>[fix]</strong> creating the WP schedule entry => scheduled backup issue</li>
<li><strong>[improvement]</strong> moved tmp/logs to wp-content/uploads directory</li>
<li><strong>[fix]</strong> character encoding on WebDav storage provider (default UTF8) </li></ul>
<span>0.2.3-32</span><ul><li><strong>[update]</strong> WP 4.5 compatibility tests</li></ul>
<span>0.2.3-31</span><ul><li><strong>[fix]</strong> removed some accidentally forgoten debug lines of code (Oops)</li>
<li><strong>[tweak]</strong> show a notice when activating Pro over Free version or vice-versa</li>
</ul>
<span>0.2.3-27</span><ul><li><strong>[fix]</strong> job statistics when OS cannot provide CPU/memory info</li>
<li><strong>[fix]</strong> pre-restore checks of a MySQL database backup</li>
<li><strong>[new]</strong> added an installation notice on WordPress dashboard</li>
<li><strong>[updated]</strong> set the default `Upload max chunk size` to 256KiB</li></ul>
<span>0.2.3-21</span><ul><li><strong>[change]</strong> Version change log has changed from Git commit messages to an `user friendly` description</li></ul>
<span>0.2.3-20</span><ul><li><strong>[new]</strong> added MySQLi and PDO MySQL support (MySQL deprecated on PHP 5.5+)</li>
<li><strong>[new]</strong> added dashboard backup & restore brief statistics</li>
<li><strong>[new]</strong> automatic clean-up (each 24h) of residual files left after unusual exits</li>
<li><strong>[new]</strong> automatic unlock of the job locking file if exceeds 24h</li>

<li><strong>[new]</strong> MySQL support for connections using alternate port and/or Unix socket and Named Pipes </li>
<li><strong>[improvement]</strong> usage of prepared SQL statement (increased speed & security)</li>
<li><strong>[improvement]</strong> added alternative progress for browsers/servers not supporting buffered output</li>
<li><strong>[improvement]</strong> performance improvement for job console screen</li>
<li><strong>[improvement]</strong> enhanced UI feedback during backup job (useful for backups with 5K+ files)</li>
<li><strong>[improvement]</strong> improved overall UI responsiveness (50% faster)</li>
<li><strong>[improvement]</strong> optimized the backup filelist creation (useful for 5K+ files and slow systems)</li>


<li><strong>[change]</strong> renamed `Backup targets` tab name to `Copy backup to`</li>


<li><strong>[change]</strong> renamed `WP backup` tab name to `WP backup job`</li>
<li><strong>[change]</strong> renamed `Backup Schedule` tab name to `Backup Scheduler`</li>
<li><strong>[change]</strong> renamed `Logs` tab name to `Log files`</li>
<li><strong>[change]</strong> renamed `Change log` tab name to `Version change log`</li>
<li><strong>[update]</strong> added `tar` to the default `Exclude files by extension` list</li>
<li><strong>[fix]</strong> fixed the backup console progress bar while compressing using the ZIP filter</li></ul>
<span>0.2.3-9</span><ul><li><strong>[fix]</strong> export the MySQL DB as XML</li>
<li><strong>[fix]</strong> plug-in not usable when PHP's `open_basedir` option was in effect</li>
<li><strong>[update]</strong> changed some scrambled CURL error messages (eg: Curl error #7)</li>
<li><strong>[new]</strong> added various alerts based on the app/system configuration </li>
<li><strong>[tweak]</strong> several UI enhancements</li>
</ul>
<span>0.2.3-4</span><ul><li><strong>[fix]</strong> export MySQL DB as XML</li></ul>
<span>0.2.3-2</span><ul><li><strong>[improvement]</strong> various plug-in core libs enhancements</li></ul>
<span>0.2.3-1</span><ul><li><strong>[improvement]</strong> added `CPU throttling` feature to overcome the `CPU Limit Exceeded` on free hosting</li>
<li><strong>[improvement]</strong> update the `How it works` user guide</li></ul>
<span>0.2.3</span><ul><li><strong>[improvement]</strong> added restore feature </li>
<li><strong>[improvement]</strong> added dashboard screen (quick backup/restore/log view)</li>
<li><strong>[improvement]</strong> automatic backup of plugins/themese/mysql on WordPress upgrade</li>
<li><strong>[tweak]</strong> added "How it works" link to the user guide</li>
<li><strong>[update]</strong> updated the user guide</li>
<li><strong>[fix]</strong> CURLOPT_FOLLOWLOCATION cannot be activated when in safe_mode or an open_basedir is set in</li>
<li><strong>[fix]</strong> fixed export settings option</li>
<li><strong>[fix]</strong> the excluded directories in backup/WP source file list not shown unchecked</li>
</ul>
<span>0.2.2-5</span><ul>
<li><strong>[fix]</strong> detection of valid archive for non BZip|GZip archives</li>
<li><strong>[fix]</strong> restoring Dropbox|Google authorization + logs after version upgrade    </li></ul>
<span>0.2.2-4</span><ul><li><strong>[fix]</strong> dashboard performance issue on WP Ajax Heartbeat (admin-ajax.php)</li>
<li><strong>[fix]</strong> plug-in banners not shown on Chrome due to self-signed SSL certificate error (ERR_INSECURE_RESPONSE)</li>
<li><strong>[fix]</strong> divergent count of the scheduled backup files vs actual backed up files</li>
<li><strong>[update]</strong> prevent the collition of the global variable name of autoloader class</li>
<li><strong>[update]</strong> updated the default file extension list excluded from backup (gz,bz,bz2)</li>
<li><strong>[update]</strong> updated the Welcome screen and the `How to` documentation</li>
<li><strong>[tweak]</strong> added new backup expert option `script memory limit` </li>
<li><strong>[tweak]</strong> set the min/max allowed value on some UI numeric inputs (like ports, retention, etc)</li>
<li><strong>[tweak]</strong> show relative path instead full path on WP Source file list (shorter=&gt;faster)</li>
<li><strong>[tweak]</strong> stripped the Windows drive letter from the filepath included into a .tar(bz|gz) archive</li>
<li><strong>[tweak]</strong> new backup expert option which allows the usage of file relative path instead full path</li>
<li><strong>[tweak]</strong> Logs tab shows now the log file size for each individual log</li>
<li><strong>[improvement]</strong> integration of WP MyBackup with iThemes Security plug-in</li>
<li><strong>[improvement]</strong> compatibility with WordPress Multisite/Network (access restricted to blog files/db only)</li>
<li><strong>[improvement]</strong> added ZIP archive support via WP PclZip (requires zlib extension)</li>
<li><strong>[improvement]</strong> new option to avoid compression by extension (jpg,jpeg,png,gif,mp3,mp4,mpg,mpeg,avi,mov,qt,mkv,wmv,asf,m2v,m4v,rm)</li>
<li><strong>[improvement]</strong> Database WYSIWYG table selector (creates automatically the compound regex pattern)</li>
<li><strong>[improvement]</strong> new Support expert option to detect extra whitespaces that may affect the browser's direct file download</li>
</ul>
<span>0.2.2-3</span><ul><li><strong>[fix]</strong> backup plugin's tmp files on plugin update instead of framework upgrade</li>
<li><strong>[improvement]</strong> enclosed constant definitions within namespace(prevents collision)</li>
</ul>
</div></div>
	

== Upgrade Notice ==

Upgrade WP MyBackup to the latest version to make sure you benefit the latest improvements and bug fixes.
   
== Translations ==

* English - default, always included

*Note:* The plugin is localized/translatable by default. Please contribute your language to the plugin to make it even more useful. For translating I recommend the ["PoEdit" application](http://poedit.net/).
