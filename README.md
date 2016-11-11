# torrentapi

A private torrent database API for caching and web development projects.

## API usage

### GET

Parameter | Type   | Required
--------- | ------ | --------
api_key   | string | true
mode      | string | true
id        | int    | true

#### Modes
__f | file | download__ (getting a torrent file)

_URL Structure_  
`GET /?api_key={api_key}&mode={f|file|download}&id={id} HTTP/1.1`
`GET /{api_key}/{f|file|download}/{id}/ HTTP/1.1`

__i | info | information__ (getting a torrent's information)

_URL Structure_  
`GET /?api_key={api_key}&mode={i|info|information}&id={id} HTTP/1.1`
`GET /{api_key}/{i|info|information}/ HTTP/1.1`

### POST

Parameter | Type   | Required
--------- | ------ | --------
api_key   | string | true
mode      | string | true

#### Modes
__u | uload | upload__ (uploading a torrent file)

_URL Structure_  
`POST / HTTP/1.1`

_Values_
```
api_key: {api_key}
mode: u|uload|upload
torrent_file: {torrent_file}
```
