# PlayerTestV02

Server Structure:
- /Music
- /PlayerTest
- /PlayerTest/music_cover
- /PlayerTest/playlist_cover
- /PlayerTest/user_cover
- /PlayerTest/playlistJsonBackup
- /PlayerTest/crud.php
- /PlayerTest/get_music163_playlist.py

Database file: player_test_2_db.sql

Collect playlist to database: run $ python get_music163_playlist.py [playlist id from music163]

Before the collection, download all songs of the playlist to /Music first.

Music naming format: [artist] - [title].mp3

Setting/IP address must match server's.
