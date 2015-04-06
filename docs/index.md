# PayPerWin API

Version 1.0

*Base URL:* `https://payperwin.gg/api/v1/`

## Streamer Pledges

Use this request to get a list of latest pledges, ordered latest to earliest, for the specified Twitch streamer.

### Request Format

*Method:* GET

*URI:* `streamers/<twitch-username>/pledges`

*Query Parameters:*
- `after`: the date as a unix timestamp after which the pledges should have been created, not including the date itself (no default)
- `limit`: the number of results per page (default `50`, max `500`)
- `page`: the page from which to start, starting from 1 (default `1`)

#### Example Request

```
https://payperwin.gg/api/v1/streamers/imaqtpie/pledges?after=1427825237&limit=10&page=3
```

### Response Format

*HTTP Status:* `200 OK`

*Header:* `Content-type: application/json`

*Content:* the returned content contains the following field structure:
- `pledges`: (array) list of pledges, each having:

  - `id`: (integer) the pledge's unique identifier
  - `type`: (string) the 
  - `amount`: (number) amount in USD that the streamer will earn from each each winning game
  - `message`: (string or null) a message - if any - that the user left for the streamer when pledging
  - `end_date`: (integer or null) the date until which the pledge will remain active as a unix timestamp
  - `win_limit`: (integer or null) the maximum number of wins for which the pledge will remain active
  - `spending_limit`: (integer or null) the maximum amount spent in USD for which the pledge will remain active
  - `user`: (string) the PayPerWin public username of the person making the pledge
  - `running`: (boolean) whether or not the pledge is active
  - `created_at`: (integer) the pledge's creation date as a unix timestamp

#### Example Response
```json
{
  "pledges":
  [
    {
      "id": 10000,
      "type": "win",
      "amount": 1,
      "message": "Kappa",
      "end_date": null,
      "win_limit": null,
      "spending_limit": null,
      "user": "ThePwnerer",
      "running": true,
      "created_at": 1428244534
    },
    {
      "id": 9999,
      "type": "win",
      "amount": 0.1,
      "message": "Go get 'em!",
      "end_date": null,
      "win_limit": 50,
      "spending_limit": null,
      "user": "True Fan",
      "running": true,
      "created_at": 1427838248
    },
    {
      "id": 9998,
      "type": "win",
      "amount": 0.25,
      "message": null,
      "end_date": null,
      "win_limit": null,
      "spending_limit": 1,
      "user": "DongSquad101",
      "running": false,
      "created_at": 1427825237
    }
  ]
}
```

### Error Format

#### Invalid Streamer

*HTTP Status:* `404 Not Found`

*Header:* `Content-type: application/json`

*Content:*

```json
{
  "error": "Not Found"
}
```

Apart from typos, the streamer specified by Twitch username might be invalid for one of several reasons:
- The user hasn't registered on PayPerWin
- The user has registered but hasn't enabled their streaming profile on PayPerWin
- The user has registered but hasn't linked their League of Legends profile

#### Invalid Request

*HTTP Status:* `400 Bad Request`

*Header:* `Content-type: application/json`

*Content:*

```json
{
  "error": "Bad Request",
  "reason":
  {
    "<field>": "<rule>"
  }
}
```

This may be thrown if one or more of the query parameters is invalid. For example:

```json
curl https://payperwin.gg/api/v1/streamers/imaqtpie/pledges?after=foo

{
  "error": "Bad Request",
  "reason":
  {
    "after": "integer"
  }
}
```


#### Server Error

*HTTP Status:* `500 Internal Server Error`

*Header:* `Content-type: application/json`

*Content:*

```json
{
  "error": "Server error"
}
```

This may be thrown when something went wrong on our end.

