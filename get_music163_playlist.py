import os, sys
import urllib
import requests
from bs4 import BeautifulSoup
import re
import time
import traceback
import pymysql

headInfo = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
    'Host': 'music.163.com'}

def isInt(string):
    try:
        temp = int(string)
        return True
    except:
        return False

def getHTMLContent(url, code='utf-8'):
    try:
        r = requests.get(url, timeout=30, headers = headInfo)
        r.raise_for_status()
        r.encoding = code
        return r.text
    except:
        print('error: getHTMLContent')
        return ''

def getPlaylist(url_base, html, playlistId):
    count = 0
    regDateYMD = re.compile(r'\d{4}[/|-]\d{2}[/|-]\d{2}')
    lyric_url_base = url_base + 'api/song/media?id='
    try:
        soup = BeautifulSoup(html, "html.parser")
        playlistInfo = {}
        playlistInfo['music_163_playlist_id'] = playlistId
        playlistInfo['cover'] = soup.select('div.cover')[0].img.attrs["src"].split('?')[0]
        playlistInfo['title'] = soup.find('h2').text
        playlistInfo['user_name'] = soup.select('.user .name a')[0].text
        playlistInfo['user_face'] = soup.select('.user .face img')[0].attrs['src'].split('?')[0]
        playlistInfo['time'] = regDateYMD.search(soup.select('.user .time')[0].text).group(0)
        playlistInfo['tags'] = []
        for i in soup.select('div .tags i'):
            playlistInfo['tags'].append(i.text)
        if len(soup.select('p#album-desc-more')) == 0:
            playlistInfo['intro'] = ""
        else:
            playlistInfo['intro'] = soup.select('p#album-desc-more')[0].text.replace('介绍：','',1).strip()
        playlistInfo['songs'] = []
        print("\r (1/3) Current Collecting Progress: {:.2f}%".format(0),end="")
        length = len(soup.select('#song-list-pre-cache li'))
        for j in soup.select('#song-list-pre-cache li'):
            song_url = url_base + j.a.attrs['href']
            lyric_url = lyric_url_base + j.a.attrs['href'].split('?id=')[1]
            temp = getSongInfo(song_url)
            temp['lyric'] = getLyric(lyric_url)
            temp['title'] = j.text
            temp['href'] = song_url
            playlistInfo['songs'].append(temp)
            count += 1
            print("\r (1/3) Current Collecting Progress: {:.2f}%".format(count/length*100),end="")
        return playlistInfo
    except:
        print('error: getPlaylist')
        traceback.print_exc()
        return ''

def getLyric(url):
    time.sleep(1)
    try:
        html = getHTMLContent(url)
        return html
    except:
        print('error: getLyric')
        traceback.print_exc()
        return {}

def getSongInfo(url):
    time.sleep(5)
    try:
        html = getHTMLContent(url)
        soup = BeautifulSoup(html, "html.parser")
        subtitle = soup.select('div.subtit')
        if len(subtitle) > 0:
            subtitle = subtitle[0].text
        else:
            subtitle = ''
        temp = soup.select('div.cnt .des')
        artist = temp[0].span.text
        album = temp[1].a.text
        image = soup.select('div.u-cover')[0].img.attrs["src"].split('?')[0]
        temp = {}
        temp['subtitle'] = subtitle
        temp['artist'] = artist
        temp['album'] = album
        temp['image'] = image
        return temp
    except:
        print('error: getSongInfo')
        traceback.print_exc()
        return {}

def output(playlist, path):
    if os.path.exists("playlist_cover") == False:
        os.mkdir("playlist_cover")
    if os.path.exists("music_cover") == False:
        os.mkdir("music_cover")
    count = 0
    db = pymysql.connect('localhost', 'root', '907881445', 'player_test_2_db', charset='utf8mb4')
    cursor = db.cursor();
    try:
        temp = str(playlist).replace('{', '{\n')
        temp = temp.replace('}', '\n}\n')
        temp = temp.replace('[', '[\n')
        temp = temp.replace(']', '\n]\n')
        temp = temp.replace(',', ',\n')
        with open(path, 'w', encoding = 'utf-8') as f:
            f.write(temp)
            f.close()
        selectPlaylistSql = "select id from playlist \
                             where title = '%s' and date = '%s';" % \
                             (playlist["title"].replace("'", "''"), playlist["time"].replace("'", "''"))
        insertPlaylistSql = "insert into playlist(title, date, introduction, tags) \
                             value ('%s', '%s', '%s', '%s');" % \
                             (playlist["title"].replace("'", "''"), playlist["time"].replace("'", "''"),
                              playlist["intro"].replace("'", "''"), str(playlist["tags"]).replace("'", "").replace("[", "").replace("]", ""))
        cursor.execute(selectPlaylistSql)
        if cursor.rowcount < 1:
            insert2Database(db, insertPlaylistSql)
        cursor.execute(selectPlaylistSql)
        playlistId = cursor.fetchone()[0]
        playlistCoverPath = "playlist_cover/" + str(playlistId) + ".jpg"
        if os.path.exists(playlistCoverPath) == False:
            time.sleep(3)
            urllib.request.urlretrieve(playlist["cover"], playlistCoverPath)
        length = len(playlist["songs"])
        print("\r (2/3) Current Storing Progress: {:.2f}%     ".format(0),end="")
        for song in playlist["songs"]:
            selectArtistSql = "select id from artist \
                               where artist_name = '%s';" % \
                               (song["artist"].replace("'", "''"))
            insertArtistSql = "insert into artist(artist_name) \
                               value ('%s');" % \
                               (song["artist"].replace("'", "''"))
            cursor.execute(selectArtistSql)
            if cursor.rowcount < 1:
                insert2Database(db, insertArtistSql)
            cursor.execute(selectArtistSql)
            artistId = cursor.fetchone()[0]
            selectAlbumSql = "select id from album \
                              where album_name = '%s';" % \
                               (song["album"].replace("'", "''"))
            insertAlbumSql = "insert into album(album_name, artist_id) \
                              value ('%s', '%d');" % \
                              (song["album"].replace("'", "''"), artistId)
            cursor.execute(selectAlbumSql)
            if cursor.rowcount < 1:
                insert2Database(db, insertAlbumSql)
            cursor.execute(selectAlbumSql)
            albumId = cursor.fetchone()[0]
            selectMusicSql = "select id from music \
                              where title = '%s' and subtitle = '%s';" % \
                              (song["title"].replace("'", "''"), song["subtitle"].replace("'", "''"))
            insertMusicSql = "insert into music(album_id, title, subtitle) \
                              value ('%d', '%s', '%s');" % \
                              (albumId, song["title"].replace("'", "''"), song["subtitle"].replace("'", "''"))
            cursor.execute(selectMusicSql)
            if cursor.rowcount < 1:
                insert2Database(db, insertMusicSql)
            cursor.execute(selectMusicSql)
            musicId = cursor.fetchone()[0]
            musicCoverPath = "music_cover/" + str(musicId) + ".jpg"
            if os.path.exists(musicCoverPath) == False:
                time.sleep(3)
                urllib.request.urlretrieve(song["image"], musicCoverPath)
            selectPlaylistLineSql = "select id from playlist_line \
                                     where playlist_id = '%d' and music_id = '%d';" % \
                                     (playlistId, musicId)
            insertPlaylistLineSql = "insert into playlist_line(playlist_id, music_id) \
                                    value ('%d', '%d');" % \
                                    (playlistId, musicId)
            cursor.execute(selectPlaylistLineSql)
            if cursor.rowcount < 1:
                insert2Database(db, insertPlaylistLineSql)
            musicSourcePath = "../Music/" + song["artist"] + " - " + song["title"] + ".mp3"
            deletePlaylistLineSql = "delete from playlist_line where music_id = '%d'" % \
                                     (musicId)
            deleteUserFavouriteSql = "delete from user_favourite where music_id = '%d'" % \
                                      (musicId)
            deleteMusicSql = "delete from music where id = '%d'" % \
                              (musicId)
            if os.path.exists(musicSourcePath) == False:
                deleteRecord(db, deletePlaylistLineSql)
                deleteRecord(db, deleteUserFavouriteSql)
                deleteRecord(db, deleteMusicSql)
            selectLyricsSql = "select * from lyrics \
                               where music_id = '%d';" % \
                               (musicId)
            insertLyricsSql = "insert into lyrics(music_id, lyric) \
                               value ('%d', '%s');" % \
                               (musicId, song["lyric"].replace("'", "''"))
            cursor.execute(selectLyricsSql)
            if cursor.rowcount < 1:
                insert2Database(db, insertLyricsSql)
            count += 1
            print("\r (2/3) Current Storing Progress: {:.2f}%     ".format(count/length*100),end="")
    except:
        traceback.print_exc()
    db.close()

def deleteRecord(db, sql):
    cursor = db.cursor();
    try:
        cursor.execute(sql)
        db.commit()
    except:
        db.rollback()
        traceback.print_exc()

def insert2Database(db, sql):
    cursor = db.cursor();
    try:
        cursor.execute(sql)
        db.commit()
    except:
        db.rollback()
        traceback.print_exc()

def cleanDatabase():
    db = pymysql.connect('localhost', 'root', '907881445', 'player_test_2_db', charset='utf8mb4')
    cursor = db.cursor();
    count = 0
    try:
        selectMusicSql = "SELECT art.artist_name, am.title, am.music_id FROM artist art INNER JOIN ( \
                            SELECT m.id as music_id, m.album_id, m.title, m.subtitle, alb.album_name, alb.artist_id \
                            FROM album alb INNER JOIN music m ON m.album_id = alb.id \
                          ) am ON am.artist_id = art.id ORDER BY am.music_id"
        cursor.execute(selectMusicSql)
        results = cursor.fetchall()
        length = cursor.rowcount
        print("\r (3/3) Current Database Cleaning Progress: {:.2f}%  ".format(0),end="")
        for row in results:
            deletePlaylistLineSql = "delete from playlist_line where music_id = '%d'" % \
                                     (row[2])
            deleteUserFavouriteSql = "delete from user_favourite where music_id = '%d'" % \
                                      (row[2])
            deleteMusicSql = "delete from music where id = '%d'" % \
                              (row[2])
            musicPath = "../Music/" + row[0] + " - " + row[1] + ".mp3"
            if os.path.exists(musicPath) == False:
                deleteRecord(db, deletePlaylistLineSql)
                deleteRecord(db, deleteUserFavouriteSql)
                deleteRecord(db, deleteMusicSql)
            count += 1
            print("\r (3/3) Current Database Cleaning Progress: {:.2f}%  ".format(count/length*100),end="")
    except:
        traceback.print_exc()
    db.close()

def main(passedId):
    id = ''
    if isInt(passedId):
        id = passedId
    else:
        raise Exception("error: param integer expected")
    path = 'playlistJsonBackup/' + passedId + '.js'
    url_base = 'http://music.163.com/'
    playlist_url_base = url_base + 'playlist?id='
    html = getHTMLContent(playlist_url_base + id)
    playlist = getPlaylist(url_base, html, id)
    output(playlist, path)
    cleanDatabase()

cmdParams = len(sys.argv)
if (cmdParams > 2):
    raise Exception("error: more than 1 params")
elif (cmdParams < 2):
    raise Exception("error: no params")
else:
    main(sys.argv[1])
