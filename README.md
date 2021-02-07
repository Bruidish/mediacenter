# Mediacenter
An easy way to display your films collection and populate their informations


## Captures
### Home
![homepage](assets/img/readme/capture-home.png)  

### Modal about film
![homepage](assets/img/readme/capture-modal-1.png)  

### Modal about video file
![homepage](assets/img/readme/capture-modal-2.png)  


## Requirements
* [ffmpeg](https://www.ffmpeg.org/) to encode video in mp4


## Install
* Put files into your server  
* Create database table and set it in [Configuration file](config/config.json)  
* Go to the localhost url of the project  
* Drag and drop on window an avi|mkv|mp4 file  

## Todo
* Optimize mp4 encodding
* add tags like action, comedy, adventure, triller, series, etc.
* add comedian informations and filters
* add movie during
* add private comment
* chunk large upload  
@see https://gist.github.com/shiawuen/1534477   
at time to upload large file we ahve to edit php.ini with something like :
```bash
upload_max_filesize = 8000M
post_max_size = 8000M
```

