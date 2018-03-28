<?php

header('Access-Control-Allow-Origin:*');

$servername = "localhost";
$username = "root";
$password = "907881445";
$dbname = "player_test_2_db";

$conn = mysqli_connect($servername, $username, $password, $dbname) or
        die("Connection failed: " . mysqli_connect_error());

mysqli_set_charset($conn, "utf8");

$PURPOSE = [
    "loadLibrary",
    "loadPlaylist",
    "loadSearchResultByTitle",
    "loadSearchResultByArtist",
    "loadSearchResultByPlaylist",
    "loadMusicByPlaylist",
    "loadMusic",
    "login",
    "loadMyMessages",
    "setMyMessagesRead",
    "setInfo",
    "loadNews",
    "checkMyMessages",
    "loadUser",
    "insertNews",
    "loadFavourite",
    "toggleFavourite",
    "loadCreatedPlaylist",
    "loadCollectedPlaylist",
    "loadMusicToCreatePlaylist",
    "insertPlaylist",
    "loadPlaylistToCollectPlaylist",
    "updateUserCollectedPlaylist",
    "loadLocalMusic",
    "addAll2Favourite",
    "getLyric"
];

if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[0]) {
    $sql = "SELECT id, title FROM playlist ORDER BY id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $json = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $json[] = array(
                "id" => $row["id"],
                "title" => $row["title"]
            );
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "0";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[1]) {
    $id = filter_input(INPUT_GET, 'id');
    $selectPlaylistSql = "SELECT p.title, p.date, p.introduction, p.tags, u.user_name "
            . "FROM playlist p "
            . "INNER JOIN user u "
            . "ON p.user_id = u.id "
            . "WHERE p.id = '$id'";
    $selectMusicSql = "SELECT m.title, m.subtitle, pl.music_id "
            . "FROM music m "
            . "INNER JOIN playlist_line pl "
            . "ON m.id = pl.music_id "
            . "WHERE pl.playlist_id = '$id' "
            . "ORDER BY pl.music_id";
    $resultPlaylist = mysqli_query($conn, $selectPlaylistSql);
    $resultMusic = mysqli_query($conn, $selectMusicSql);

    $json = array(
        "playlist" => null,
        "music" => null
    );

    $error_flag = false;
    if (mysqli_num_rows($resultPlaylist) == 1) {
        while ($row = mysqli_fetch_assoc($resultPlaylist)) {
            $json["playlist"] = array(
                "title" => $row["title"],
                "date" => $row["date"],
                "intro" => $row["introduction"],
                "tags" => $row["tags"],
                "user" => $row["user_name"]
            );
        }
    } else {
        $error_flag = true;
    }
    if (mysqli_num_rows($resultMusic) > 0) {
        while ($row = mysqli_fetch_assoc($resultMusic)) {
            $json["music"][$row["music_id"]] = array(
                "title" => $row["title"],
                "subtitle" => $row["subtitle"]
            );
        }
    } else {
        $error_flag = true;
    }
    if ($error_flag) {
        echo "1";
    } else {
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[2]) {
    $keyword = filter_input(INPUT_GET, 'keyword');
    $selectMusicSql = "SELECT id, title, subtitle "
            . "FROM music "
            . "WHERE title LIKE '%$keyword%' OR subtitle LIKE '%$keyword%' "
            . "ORDER BY id";
    $resultMusic = mysqli_query($conn, $selectMusicSql);

    $json = [];

    if (mysqli_num_rows($resultMusic) > 0) {
        while ($row = mysqli_fetch_assoc($resultMusic)) {
            $json[] = array(
                "id" => $row["id"],
                "title" => $row["title"],
                "subtitle" => $row["subtitle"]
            );
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "2";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[3]) {
    $keyword = filter_input(INPUT_GET, 'keyword');
    $selectMusicSql = "SELECT m.id, m.title, m.subtitle "
            . "FROM music m "
            . "INNER JOIN ("
            . "SELECT alb.id, art.artist_name "
            . "FROM artist art "
            . "INNER JOIN album alb "
            . "ON art.id = alb.artist_id"
            . ") aa "
            . "ON m.album_id = aa.id "
            . "WHERE aa.artist_name LIKE '%$keyword%' "
            . "ORDER BY m.id";
    $resultMusic = mysqli_query($conn, $selectMusicSql);

    $json = [];

    if (mysqli_num_rows($resultMusic) > 0) {
        while ($row = mysqli_fetch_assoc($resultMusic)) {
            $json[] = array(
                "id" => $row["id"],
                "title" => $row["title"],
                "subtitle" => $row["subtitle"]
            );
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "3";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[4]) {
    $keyword = filter_input(INPUT_GET, 'keyword');
    $selectMusicSql = "SELECT p.id, p.title, p.tags "
            . "FROM playlist p "
            . "WHERE p.title LIKE '%$keyword%' OR p.tags LIKE '%$keyword%' "
            . "ORDER BY p.id";
    $resultMusic = mysqli_query($conn, $selectMusicSql);

    $json = [];

    if (mysqli_num_rows($resultMusic) > 0) {
        while ($row = mysqli_fetch_assoc($resultMusic)) {
            $json[] = array(
                "id" => $row["id"],
                "title" => $row["title"],
                "subtitle" => $row["tags"]
            );
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "4";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[5]) {
    $id = filter_input(INPUT_GET, 'id');
    $selectMusicSql = "SELECT * "
            . "FROM artist art "
            . "INNER JOIN ( "
            . "SELECT mpl.album_id, mpl.title, mpl.subtitle, mpl.music_id, alb.album_name, alb.artist_id "
            . "FROM album alb "
            . "INNER JOIN ( "
            . "SELECT m.album_id, m.title, m.subtitle, pl.music_id "
            . "FROM music m "
            . "INNER JOIN playlist_line pl "
            . "ON m.id = pl.music_id "
            . "WHERE pl.playlist_id = '$id' "
            . ") mpl "
            . "ON mpl.album_id = alb.id "
            . ") ampl "
            . "ON ampl.artist_id = art.id "
            . "ORDER BY ampl.music_id";
    $resultMusic = mysqli_query($conn, $selectMusicSql);

    $json = [];

    if (mysqli_num_rows($resultMusic) > 0) {
        while ($row = mysqli_fetch_assoc($resultMusic)) {
            $json[] = array(
                "artist_id" => $row["artist_id"],
                "artist_name" => $row["artist_name"],
                "album_id" => $row["album_id"],
                "album_name" => $row["album_name"],
                "id" => $row["music_id"],
                "title" => $row["title"],
                "subtitle" => $row["subtitle"]
            );
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "5";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[6]) {
    $id = filter_input(INPUT_GET, 'id');
    $selectMusicSql = "SELECT * "
            . "FROM artist art "
            . "INNER JOIN ( "
            . "SELECT m.id as music_id, m.album_id, m.title, m.subtitle, alb.album_name, alb.artist_id "
            . "FROM album alb "
            . "INNER JOIN music m "
            . "ON m.album_id = alb.id "
            . "WHERE m.id = '$id' "
            . ") am "
            . "ON am.artist_id = art.id "
            . "ORDER BY am.music_id";
    $resultMusic = mysqli_query($conn, $selectMusicSql);

    $json = null;

    if (mysqli_num_rows($resultMusic) == 1) {
        while ($row = mysqli_fetch_assoc($resultMusic)) {
            $json = array(
                "artist_id" => $row["artist_id"],
                "artist_name" => $row["artist_name"],
                "album_id" => $row["album_id"],
                "album_name" => $row["album_name"],
                "id" => $row["music_id"],
                "title" => $row["title"],
                "subtitle" => $row["subtitle"]
            );
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "6";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[7]) {
    $json = null;

    if (filter_input(INPUT_GET, 'token') != null) {
        $id = filter_input(INPUT_GET, 'id');
        $token = filter_input(INPUT_GET, 'token');
        $selectUserSql = "SELECT * FROM user WHERE id = '$id' AND token = '$token'";
        $resultUser = mysqli_query($conn, $selectUserSql);

        if (mysqli_num_rows($resultUser) == 1) {
            $row = mysqli_fetch_assoc($resultUser);
            $userName = $row["user_name"];
            $selectNewsSql = "SELECT * FROM news WHERE content LIKE '%@$userName%' ORDER BY createDate DESC";
            $resultNews = mysqli_query($conn, $selectNewsSql);
            $news = [];

            while ($j = mysqli_fetch_assoc($resultNews)) {
                $selectUserNameSql = "SELECT user_name FROM user WHERE id = '$id'";
                $resultUserName = mysqli_query($conn, $selectUserNameSql);

                $news[] = array(
                    "id" => $j["id"],
                    "content" => $j["content"],
                    "date" => $j["createDate"],
                    "name" => mysqli_fetch_assoc($resultUserName)["user_name"],
                    "isRead" => $j["is_read"]
                );
            }

            $json = array(
                "talk" => $row["user_talks"],
                "messages" => $news
            );
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
        } else {
            echo "7";
        }
    } else {
        $account = filter_input(INPUT_GET, 'account');
        $password = filter_input(INPUT_GET, 'password');
        $selectUserSql = "SELECT * FROM user WHERE user_account = '$account' AND user_password = '$password'";
        $resultUser = mysqli_query($conn, $selectUserSql);

        if (mysqli_num_rows($resultUser) == 1) {
            while ($row = mysqli_fetch_assoc($resultUser)) {
                $token = "";
                $userName = $row["user_name"];
                for ($i = 0; $i < 128; $i++) {
                    $token .= chr(rand(65, 90)) . chr(rand(97, 122)) . chr(rand(48, 57)) . chr(rand(97, 122));
                }
                $updateUserSql = "UPDATE user SET token = '$token' WHERE id = '" . $row["id"] . "'";
                mysqli_query($conn, $updateUserSql);

                $selectNewsSql = "SELECT * FROM news WHERE content LIKE '%@$userName%' ORDER BY createDate DESC";
                $resultNews = mysqli_query($conn, $selectNewsSql);
                $news = [];

                while ($j = mysqli_fetch_assoc($resultNews)) {
                    $userId = $j["user_id"];
                    $selectUserNameSql = "SELECT user_name FROM user WHERE id = '$userId'";
                    $resultUserName = mysqli_query($conn, $selectUserNameSql);

                    $news[] = array(
                        "id" => $j["id"],
                        "content" => $j["content"],
                        "date" => $j["createDate"],
                        "name" => mysqli_fetch_assoc($resultUserName)["user_name"],
                        "isRead" => $j["is_read"]
                    );
                }

                $json = array(
                    "id" => $row["id"],
                    "name" => $row["user_name"],
                    "level" => $row["user_level"],
                    "talk" => $row["user_talks"],
                    "token" => $token,
                    "messages" => $news
                );
            }
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
        } else {
            echo "7";
        }
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[8]) {
    $name = filter_input(INPUT_GET, 'name');
    $selectNewsSql = "SELECT * FROM news WHERE content LIKE '%@$name%' ORDER BY createDate DESC";
    $resultNews = mysqli_query($conn, $selectNewsSql);

    if (mysqli_num_rows($resultNews) > 0) {
        $news = [];
        while ($row = mysqli_fetch_assoc($resultNews)) {
            $userId = $row["user_id"];
            $selectUserNameSql = "SELECT user_name FROM user WHERE id = '$userId'";
            $resultUserName = mysqli_query($conn, $selectUserNameSql);

            $news[] = array(
                "content" => $row["content"],
                "name" => mysqli_fetch_assoc($resultUserName)["user_name"],
                "isRead" => $row["is_read"]
            );
        }
        echo json_encode($news, JSON_UNESCAPED_UNICODE);
    } else {
        echo "8";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[9]) {
    $name = filter_input(INPUT_GET, 'name');
    $updateNewsSql = "UPDATE news SET is_read = 1 WHERE content LIKE '%@$name%'";

    if (!mysqli_query($conn, $updateNewsSql)) {
        echo "9";
    } else {
        echo "true";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[10]) {
    $id = filter_input(INPUT_GET, 'id');
    $name = filter_input(INPUT_GET, 'name');
    $oldPassword = filter_input(INPUT_GET, 'oldPassword');
    $newPassword = filter_input(INPUT_GET, 'newPassword');
    if (trim($newPassword) == "") {
        $newPassword = $oldPassword;
    }

    $selectUserSql = "SELECT * FROM user WHERE id = '$id' AND user_password = '$oldPassword'";
    $resultUser = mysqli_query($conn, $selectUserSql);

    if (mysqli_num_rows($resultUser) == 1) {
        $updateUserSql = "UPDATE user SET user_name = '$name', user_password = '$newPassword' WHERE id = '$id'";
        if (mysqli_query($conn, $updateUserSql)) {
            echo "true";
        } else {
            echo "10";
        }
    } else {
        echo "10";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[11]) {
    $lastDate = filter_input(INPUT_GET, 'lastDate');
    if ($lastDate == null) {
        $selectNewsSql = "SELECT * FROM news ORDER BY createDate DESC";
        $resultNews = mysqli_query($conn, $selectNewsSql);

        $json = [];

        if (mysqli_num_rows($resultNews) > 0) {
            while ($row = mysqli_fetch_assoc($resultNews)) {
                $id = $row["user_id"];
                $selectUserSql = "SELECT user_name FROM user WHERE id = '$id'";
                $resultUser = mysqli_query($conn, $selectUserSql);

                if (preg_match("/^(\[music\])(\d+)(\[\/music\])/", $row["content"], $matches) == 1) {
                    $musicId = $matches[2];
                    $selectMusicSql = "SELECT * "
                            . "FROM artist art "
                            . "INNER JOIN ( "
                            . "SELECT m.id as music_id, m.album_id, m.title, m.subtitle, alb.album_name, alb.artist_id "
                            . "FROM album alb "
                            . "INNER JOIN music m "
                            . "ON m.album_id = alb.id "
                            . "WHERE m.id = '$musicId' "
                            . ") am "
                            . "ON am.artist_id = art.id "
                            . "ORDER BY am.music_id";
                    $resultMusic = mysqli_query($conn, $selectMusicSql);

                    $music = null;
                    while ($row2 = mysqli_fetch_assoc($resultMusic)) {
                        $music = array(
                            "artist_id" => $row2["artist_id"],
                            "artist_name" => $row2["artist_name"],
                            "album_id" => $row2["album_id"],
                            "album_name" => $row2["album_name"],
                            "id" => $row2["music_id"],
                            "title" => $row2["title"],
                            "subtitle" => $row2["subtitle"]
                        );
                    }
                    $json[] = array(
                        "word" => preg_replace("/^\[music\]\d+\[\/music\]/", "", $row["content"]),
                        "date" => $row["createDate"],
                        "name" => mysqli_fetch_assoc($resultUser)["user_name"],
                        "id" => $id,
                        "music" => $music
                    );
                } else {
                    $json[] = array(
                        "word" => $row["content"],
                        "date" => $row["createDate"],
                        "name" => mysqli_fetch_assoc($resultUser)["user_name"],
                        "id" => $id
                    );
                }
            }
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
        } else {
            echo "11";
        }
    } else {
        $selectNewsSql = "SELECT * FROM news WHERE createDate > '$lastDate' ORDER BY createDate DESC";
        $resultNews = mysqli_query($conn, $selectNewsSql);

        $json = [];

        if (mysqli_num_rows($resultNews) > 0) {
            while ($row = mysqli_fetch_assoc($resultNews)) {
                $id = $row["user_id"];
                $selectUserSql = "SELECT user_name FROM user WHERE id = '$id'";
                $resultUser = mysqli_query($conn, $selectUserSql);

                if (preg_match("/^(\[music\])(\d+)(\[\/music\])/", $row["content"], $matches) == 1) {
                    $musicId = $matches[2];
                    $selectMusicSql = "SELECT * "
                            . "FROM artist art "
                            . "INNER JOIN ( "
                            . "SELECT m.id as music_id, m.album_id, m.title, m.subtitle, alb.album_name, alb.artist_id "
                            . "FROM album alb "
                            . "INNER JOIN music m "
                            . "ON m.album_id = alb.id "
                            . "WHERE m.id = '$musicId' "
                            . ") am "
                            . "ON am.artist_id = art.id "
                            . "ORDER BY am.music_id";
                    $resultMusic = mysqli_query($conn, $selectMusicSql);

                    $music = null;
                    while ($row2 = mysqli_fetch_assoc($resultMusic)) {
                        $music = array(
                            "artist_id" => $row2["artist_id"],
                            "artist_name" => $row2["artist_name"],
                            "album_id" => $row2["album_id"],
                            "album_name" => $row2["album_name"],
                            "id" => $row2["music_id"],
                            "title" => $row2["title"],
                            "subtitle" => $row2["subtitle"]
                        );
                    }
                    $json[] = array(
                        "word" => $row["content"],
                        "date" => $row["createDate"],
                        "name" => mysqli_fetch_assoc($resultUser)["user_name"],
                        "id" => $id,
                        "music" => $music
                    );
                } else {
                    $json[] = array(
                        "word" => preg_replace("/^\[music\]\d+\[\/music\]/", "", $row["content"]),
                        "date" => $row["createDate"],
                        "name" => mysqli_fetch_assoc($resultUser)["user_name"],
                        "id" => $id
                    );
                }
            }
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
        } else {
            echo "11";
        }
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[12]) {
    $name = filter_input(INPUT_GET, 'name');
    $selectNewsSql = "SELECT * FROM news WHERE content LIKE '%@$name%' AND is_read = '0'";
    $resultNews = mysqli_query($conn, $selectNewsSql);
    echo mysqli_num_rows($resultNews);
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[13]) {
    $keyword = filter_input(INPUT_GET, 'keyword');
    $selectUserSql = "SELECT user_name FROM user WHERE user_name LIKE '%$keyword%' ORDER BY user_name ASC";
    $resultUser = mysqli_query($conn, $selectUserSql);

    if (mysqli_num_rows($resultUser) > 0) {
        $json = [];
        while ($row = mysqli_fetch_assoc($resultUser)) {
            $json[] = $row["user_name"];
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "13";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[14]) {
    $id = filter_input(INPUT_GET, 'id');
    $content = filter_input(INPUT_GET, 'content');
    $insertNewsSql = "INSERT INTO news (id, content, createDate, user_id, is_read) VALUES (NULL, '$content', CURRENT_TIME(), '$id', '0')";
    $updateUserSql = "UPDATE user SET user_talks += 1 WHERE id = '$id'";
    if (mysqli_query($conn, $insertNewsSql) && mysqli_query($conn, $updateUserSql)) {
        echo "true";
    } else {
        echo "14";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[15]) {
    $id = filter_input(INPUT_GET, 'id');
    $selectUserFavouriteSql = "SELECT * FROM user_favourite WHERE user_id = '$id'";
    $resultUserFavourite = mysqli_query($conn, $selectUserFavouriteSql);

    $json = [];

    if (mysqli_num_rows($resultUserFavourite) > 0) {
        while ($row = mysqli_fetch_assoc($resultUserFavourite)) {
            $musicId = $row["music_id"];
            $selectMusicSql = "SELECT * "
                    . "FROM artist art "
                    . "INNER JOIN ( "
                    . "SELECT m.id as music_id, m.album_id, m.title, m.subtitle, alb.album_name, alb.artist_id "
                    . "FROM album alb "
                    . "INNER JOIN music m "
                    . "ON m.album_id = alb.id "
                    . "WHERE m.id = '$musicId' "
                    . ") am "
                    . "ON am.artist_id = art.id "
                    . "ORDER BY am.music_id";
            $resultMusic = mysqli_query($conn, $selectMusicSql);
            while ($row1 = mysqli_fetch_assoc($resultMusic)) {
                $json[] = array(
                    "artist_id" => $row1["artist_id"],
                    "artist_name" => $row1["artist_name"],
                    "album_id" => $row1["album_id"],
                    "album_name" => $row1["album_name"],
                    "id" => $row1["music_id"],
                    "title" => $row1["title"],
                    "subtitle" => $row1["subtitle"]
                );
            }
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "15";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[16]) {
    $userId = filter_input(INPUT_GET, 'userId');
    $musicId = filter_input(INPUT_GET, 'musicId');
    $selectUserFavouriteSql = "SELECT * FROM user_favourite WHERE user_id = '$userId' AND music_id = '$musicId'";
    $resultUserFavourite = mysqli_query($conn, $selectUserFavouriteSql);

    if (mysqli_num_rows($resultUserFavourite) == 1) {
        $id = mysqli_fetch_assoc($resultUserFavourite)["id"];
        $deleteUserFavouriteSql = "DELETE FROM user_favourite WHERE id = '$id'";
        if (mysqli_query($conn, $deleteUserFavouriteSql)) {
            echo "deleted";
        } else {
            echo "16";
        }
    } else {
        $insertUserFavouriteSql = "INSERT INTO user_favourite (id, user_id, music_id) VALUES (NULL, '$userId', '$musicId')";
        if (mysqli_query($conn, $insertUserFavouriteSql)) {
            echo "inserted";
        } else {
            echo "16";
        }
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[17]) {
    $id = filter_input(INPUT_GET, 'id');
    $selectPlaylistSql = "SELECT * FROM playlist WHERE user_id = '$id'";
    $resultPlaylist = mysqli_query($conn, $selectPlaylistSql);

    $json = [];

    if (mysqli_num_rows($resultPlaylist) > 0) {
        while ($row = mysqli_fetch_assoc($resultPlaylist)) {
            $playlistId = $row["id"];
            $selectPlaylistLineSql = "SELECT * FROM playlist_line WHERE playlist_id = '$playlistId'";
            $resultPlaylistLine = mysqli_query($conn, $selectPlaylistLineSql);

            $json[] = array(
                "id" => $row["id"],
                "title" => $row["title"],
                "amount" => mysqli_num_rows($resultPlaylistLine)
            );
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "17";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[18]) {
    $id = filter_input(INPUT_GET, 'id');
    $selectPlaylistSql = "SELECT * FROM user_collected_playlist WHERE user_id = '$id'";
    $resultPlaylist = mysqli_query($conn, $selectPlaylistSql);

    $json = [];

    if (mysqli_num_rows($resultPlaylist) > 0) {
        while ($row = mysqli_fetch_assoc($resultPlaylist)) {
            $playlistId = $row["playlist_id"];
            $selectPlaylistLineSql = "SELECT * FROM playlist_line WHERE playlist_id = '$playlistId'";
            $resultPlaylistLine = mysqli_query($conn, $selectPlaylistLineSql);
            $selectPlaylist2Sql = "SELECT * FROM playlist WHERE id = '$playlistId'";
            $resultPlaylist2 = mysqli_query($conn, $selectPlaylist2Sql);

            $json[] = array(
                "id" => $row["playlist_id"],
                "title" => mysqli_fetch_assoc($resultPlaylist2)["title"],
                "amount" => mysqli_num_rows($resultPlaylistLine)
            );
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "18";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[19]) {
    $selectMusicSql = "SELECT * FROM music";
    $resultMusic = mysqli_query($conn, $selectMusicSql);

    $json = [];

    if (mysqli_num_rows($resultMusic) > 0) {
        while ($row = mysqli_fetch_assoc($resultMusic)) {
            $json[] = array(
                "id" => $row["id"],
                "title" => $row["title"],
                "subtitle" => $row["subtitle"]
            );
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "19";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[20]) {
    $ids = json_decode(filter_input(INPUT_GET, 'ids'));
    $userId = filter_input(INPUT_GET, 'userId');
    $title = filter_input(INPUT_GET, 'title');
    $intro = filter_input(INPUT_GET, 'intro');
    $insertPlaylistSql = "INSERT INTO playlist (id, user_id, title, date, introduction, tags) VALUES (NULL, '$userId', '$title', CURRENT_DATE(), '$intro', '')";
    if (mysqli_query($conn, $insertPlaylistSql)) {
        $playlistId = mysqli_insert_id($conn);
        $insertPlaylistLineSql = "";
        foreach ($ids as $id) {
            $insertPlaylistLineSql .= "INSERT INTO playlist_line (id, playlist_id, music_id) VALUES (NULL, '$playlistId', '$id');";
        }
        if (mysqli_multi_query($conn, $insertPlaylistLineSql)) {
            if (copy("user_cover/$userId.jpg", "playlist_cover/$playlistId.jpg")) {
                echo "inserted";
            } else {
                echo "20";
            }
        } else {
            echo "20";
        }
    } else {
        echo "20";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[21]) {
    $id = json_decode(filter_input(INPUT_GET, 'id'));
    $selectPlaylistSql = "SELECT p.id, p.title, p.tags, IFNULL(ucp.user_id, '0') as user_id FROM playlist p "
            . "LEFT JOIN user_collected_playlist ucp "
            . "ON p.id = ucp.playlist_id "
            . "WHERE p.user_id <> '$id'";
    $resultPlaylist = mysqli_query($conn, $selectPlaylistSql);

    $json = [];

    if (mysqli_num_rows($resultPlaylist) > 0) {
        while ($row = mysqli_fetch_assoc($resultPlaylist)) {
            $json[] = array(
                "id" => $row["id"],
                "title" => $row["title"],
                "tags" => $row["tags"],
                "userId" => $row["user_id"]
            );
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "21";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[22]) {
    $ids = json_decode(filter_input(INPUT_GET, 'ids'));
    $id = filter_input(INPUT_GET, 'id');
    $deleteUserCollectedPlaylistSql = "DELETE FROM user_collected_playlist WHERE user_id = '$id'";
    if (mysqli_query($conn, $deleteUserCollectedPlaylistSql)) {
        $insertUserCollectedPlaylistSql = "";
        foreach ($ids as $playlistId) {
            $insertUserCollectedPlaylistSql .= "INSERT INTO user_collected_playlist (id, user_id, playlist_id) VALUES (NULL, '$id', '$playlistId');";
        }
        if (mysqli_multi_query($conn, $insertUserCollectedPlaylistSql)) {
            echo "updated";
        } else {
            echo "22";
        }
    } else {
        echo "22";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[23]) {
    $musicList = json_decode(filter_input(INPUT_GET, 'musicList'));
    $pass = true;
    $json = [];
    foreach ($musicList as $music) {
        $selectMusicSql = "SELECT * FROM artist art "
                . "INNER JOIN ( "
                . "SELECT m.id as music_id, m.album_id, m.title, m.subtitle, alb.album_name, alb.artist_id "
                . "FROM album alb "
                . "INNER JOIN music m "
                . "ON m.album_id = alb.id "
                . "WHERE m.title = '$music[1]' "
                . ") am "
                . "ON am.artist_id = art.id "
                . "WHERE art.artist_name = '$music[0]' "
                . "ORDER BY am.music_id";
        $resultMusic = mysqli_query($conn, $selectMusicSql);

        if (mysqli_num_rows($resultMusic) == 1) {
            while ($row = mysqli_fetch_assoc($resultMusic)) {
                $json[] = array(
                    "artist_id" => $row["artist_id"],
                    "artist_name" => $row["artist_name"],
                    "album_id" => $row["album_id"],
                    "album_name" => $row["album_name"],
                    "id" => $row["music_id"],
                    "title" => $row["title"],
                    "subtitle" => $row["subtitle"],
                    "path" => $music[2]
                );
            }
        } else {
            $pass = false;
            break;
        }
    }

    if ($pass) {
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    } else {
        echo "23";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[24]) {
    $userId = filter_input(INPUT_GET, 'userId');
    $playlistId = filter_input(INPUT_GET, 'playlistId');
    $selectPlaylistLineSql = "SELECT music_id FROM playlist_line WHERE playlist_id = '$playlistId'";
    $resultPlaylistLine = mysqli_query($conn, $selectPlaylistLineSql);

    if (mysqli_num_rows($resultPlaylistLine) > 0) {
        while ($row = mysqli_fetch_assoc($resultPlaylistLine)) {
            $musicId = $row["music_id"];
            $selectUserFavouriteSql = "SELECT * FROM user_favourite WHERE user_id = '$userId' AND music_id = '$musicId'";
            $resultUserFavourite = mysqli_query($conn, $selectUserFavouriteSql);

            if (mysqli_num_rows($resultUserFavourite) == 0) {
                $insertUserFavouriteSql = "INSERT INTO user_favourite (id, user_id, music_id) VALUES (NULL, '$userId', '$musicId')";
                if (!mysqli_query($conn, $insertUserFavouriteSql)) {
                    echo "24";
                    break;
                }
            }
        }
        echo "all inserted";
    } else {
        echo "24";
    }
} else if (filter_input(INPUT_GET, 'purpose') == $PURPOSE[25]) {
    $id = filter_input(INPUT_GET, 'id');
    $selectLyricsSql = "SELECT lyric FROM lyrics WHERE music_id = '$id'";
    $resultLyrics = mysqli_query($conn, $selectLyricsSql);

    if (mysqli_num_rows($resultLyrics) == 1) {
        while ($row = mysqli_fetch_assoc($resultLyrics)) {
            $lyric = json_decode(preg_replace("/\n/", "<br>", $row["lyric"]), true);
            if ($lyric["nolyric"] || !$lyric["lyric"]) {
                echo "25";
            } else {
                $content = preg_replace("/\[\w+:\D+\]<br>/", "", $lyric["lyric"]);
                $json = preg_split("/<br>/", $content);
                echo json_encode($json, JSON_UNESCAPED_UNICODE);
            }
        }
    } else {
        echo "25";
    }
}

mysqli_close($conn);
?>