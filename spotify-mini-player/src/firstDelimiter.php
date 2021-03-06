<?php

/**
 * firstDelimiterPlaylists function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterPlaylists($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$all_playlists             = $settings->all_playlists;
	$is_alfred_playlist_active = $settings->is_alfred_playlist_active;
	$radio_number_tracks       = $settings->radio_number_tracks;
	$now_playing_notifications = $settings->now_playing_notifications;
	$max_results               = $settings->max_results;
	$alfred_playlist_uri       = $settings->alfred_playlist_uri;
	$alfred_playlist_name      = $settings->alfred_playlist_name;
	$country_code              = $settings->country_code;
	$last_check_update_time    = $settings->last_check_update_time;
	$oauth_client_id           = $settings->oauth_client_id;
	$oauth_client_secret       = $settings->oauth_client_secret;
	$oauth_redirect_uri        = $settings->oauth_redirect_uri;
	$oauth_access_token        = $settings->oauth_access_token;
	$oauth_expires             = $settings->oauth_expires;
	$oauth_refresh_token       = $settings->oauth_refresh_token;
	$display_name              = $settings->display_name;
	$userid                    = $settings->userid;
	$echonest_api_key          = $settings->echonest_api_key;

	//
	// Search playlists
	//
	$theplaylist = $words[1];
	try {
		if (mb_strlen($theplaylist) < 2) {
			$getPlaylists = "select uri,name,nb_tracks,author,username,playlist_artwork_path,ownedbyuser,nb_playable_tracks,duration_playlist,collaborative,public from playlists";
			$stmt         = $db->prepare($getPlaylists);
		} else {
			$getPlaylists = "select uri,name,nb_tracks,author,username,playlist_artwork_path,ownedbyuser,nb_playable_tracks,duration_playlist,collaborative,public from playlists where (name like :query or author like :query)";
			$stmt         = $db->prepare($getPlaylists);
			$stmt->bindValue(':query', '%' . $theplaylist . '%');
		}

		$playlists = $stmt->execute();
	}


	catch (PDOException $e) {
		handleDbIssuePdoXml($db);
		return;
	}

	$noresult = true;
	if ($query == "Playlist▹Artist radio") {
		while ($playlist = $stmt->fetch()) {
			$noresult = false;
			if ($playlist[9]) {
				$public_status = 'collaborative';
			} else {
				if ($playlist[10]) {
					$public_status = 'public';
				} else {
					$public_status = 'private';
				}
			}
			if (startswith($playlist[1], 'Artist radio for')) {
				$w->result(null, '', "🎵 " . $playlist[1], $public_status . " playlist by " . $playlist[3] . " ● " . $playlist[7] . " tracks ● " . $playlist[8], $playlist[5], 'no', null, "Playlist▹" . $playlist[0] . "▹");
			}
		}
	} elseif ($query == "Playlist▹Song radio") {
		while ($playlist = $stmt->fetch()) {
			$noresult = false;
			if ($playlist[9]) {
				$public_status = 'collaborative';
			} else {
				if ($playlist[10]) {
					$public_status = 'public';
				} else {
					$public_status = 'private';
				}
			}
			if (startswith($playlist[1], 'Song radio for')) {
				$w->result(null, '', "🎵 " . $playlist[1], $public_status . " playlist by " . $playlist[3] . " ● " . $playlist[7] . " tracks ● " . $playlist[8], $playlist[5], 'no', null, "Playlist▹" . $playlist[0] . "▹");
			}
		}
	} else {
		$savedPlaylists           = array();
		$nb_artist_radio_playlist = 0;
		$nb_song_radio_playlist   = 0;
		while ($playlist = $stmt->fetch()) {

			if (startswith($playlist[1], 'Artist radio for')) {
				$nb_artist_radio_playlist++;
				continue;
			}

			if (startswith($playlist[1], 'Song radio for')) {
				$nb_song_radio_playlist++;
				continue;
			}

			$savedPlaylists[] = $playlist;
		}

		if (mb_strlen($theplaylist) < 2) {
			if ($nb_artist_radio_playlist > 0) {
				$w->result(null, '', "Browse your artist radio playlists (" . $nb_artist_radio_playlist . " playlists)", "Display all your artist radio playlists", './images/radio_artist.png', 'no', null, "Playlist▹Artist radio");
			}
			if ($nb_song_radio_playlist > 0) {
				$w->result(null, '', "Browse your song radio playlists (" . $nb_song_radio_playlist . " playlists)", "Display all your song radio playlists", './images/radio_song.png', 'no', null, "Playlist▹Song radio");
			}
			$w->result(null, '', 'Featured Playlists', 'Browse the current featured playlists', './images/star.png', 'no', null, 'Featured Playlist▹');
		}

		foreach ($savedPlaylists as $playlist) {
			$noresult = false;
			$added    = ' ';
			if ($playlist[9]) {
				$public_status = 'collaborative';
			} else {
				if ($playlist[10]) {
					$public_status = 'public';
				} else {
					$public_status = 'private';
				}
			}
			$w->result(null, '', "🎵" . $added . $playlist[1], $public_status . " playlist by " . $playlist[3] . " ● " . $playlist[7] . " tracks ● " . $playlist[8], $playlist[5], 'no', null, "Playlist▹" . $playlist[0] . "▹");
		}
	}

	if ($noresult) {
		$w->result(null, 'help', "There is no result for your search", "", './images/warning.png', 'no', null, '');
	}
}


/**
 * firstDelimiterAlfredPlaylist function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterAlfredPlaylist($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$all_playlists             = $settings->all_playlists;
	$is_alfred_playlist_active = $settings->is_alfred_playlist_active;
	$radio_number_tracks       = $settings->radio_number_tracks;
	$now_playing_notifications = $settings->now_playing_notifications;
	$max_results               = $settings->max_results;
	$alfred_playlist_uri       = $settings->alfred_playlist_uri;
	$alfred_playlist_name      = $settings->alfred_playlist_name;
	$country_code              = $settings->country_code;
	$last_check_update_time    = $settings->last_check_update_time;
	$oauth_client_id           = $settings->oauth_client_id;
	$oauth_client_secret       = $settings->oauth_client_secret;
	$oauth_redirect_uri        = $settings->oauth_redirect_uri;
	$oauth_access_token        = $settings->oauth_access_token;
	$oauth_expires             = $settings->oauth_expires;
	$oauth_refresh_token       = $settings->oauth_refresh_token;
	$display_name              = $settings->display_name;
	$userid                    = $settings->userid;
	$echonest_api_key          = $settings->echonest_api_key;

	//
	// Alfred Playlist
	//
	$playlist = $words[1];

	$r = explode(':', $alfred_playlist_uri);

	$w->result(null, '', "Browse your Alfred playlist (" . $alfred_playlist_name . " by " . $r[2] . ")", "You can change the playlist by selecting Change your Alfred playlist below", getPlaylistArtwork($w, $alfred_playlist_uri, false), 'no', null, 'Playlist▹' . $alfred_playlist_uri . '▹');

	if ($update_in_progress == false) {
		$w->result(null, '', "Change your Alfred playlist", "Select one of your playlists below as your Alfred playlist", './images/settings.png', 'no', null, 'Alfred Playlist▹Set Alfred Playlist▹');

		if (strtolower($r[3]) != strtolower('Starred')) {
			$w->result(null, '', "Clear your Alfred Playlist", "This will remove all the tracks in your current Alfred Playlist", './images/uncheck.png', 'no', null, 'Alfred Playlist▹Confirm Clear Alfred Playlist▹');
		}
	}
}


/**
 * firstDelimiterArtists function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterArtists($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$all_playlists             = $settings->all_playlists;
	$is_alfred_playlist_active = $settings->is_alfred_playlist_active;
	$radio_number_tracks       = $settings->radio_number_tracks;
	$now_playing_notifications = $settings->now_playing_notifications;
	$max_results               = $settings->max_results;
	$alfred_playlist_uri       = $settings->alfred_playlist_uri;
	$alfred_playlist_name      = $settings->alfred_playlist_name;
	$country_code              = $settings->country_code;
	$last_check_update_time    = $settings->last_check_update_time;
	$oauth_client_id           = $settings->oauth_client_id;
	$oauth_client_secret       = $settings->oauth_client_secret;
	$oauth_redirect_uri        = $settings->oauth_redirect_uri;
	$oauth_access_token        = $settings->oauth_access_token;
	$oauth_expires             = $settings->oauth_expires;
	$oauth_refresh_token       = $settings->oauth_refresh_token;
	$display_name              = $settings->display_name;
	$userid                    = $settings->userid;
	$echonest_api_key          = $settings->echonest_api_key;

	//
	// Search artists
	//
	$artist = $words[1];

	try {
		if (mb_strlen($artist) < 2) {
			if ($all_playlists == false) {
				$getTracks = "select artist_name,artist_artwork_path,artist_uri,uri from tracks where yourmusic=1 group by artist_name" . " limit " . $max_results;
			} else {
				$getTracks = "select artist_name,artist_artwork_path,artist_uri,uri from tracks  group by artist_name" . " limit " . $max_results;
			}
			$stmt = $db->prepare($getTracks);
		} else {
			if ($all_playlists == false) {
				$getTracks = "select artist_name,artist_artwork_path,artist_uri,uri from tracks where yourmusic=1 and artist_name like :query limit " . $max_results;
			} else {
				$getTracks = "select artist_name,artist_artwork_path,artist_uri,uri from tracks where artist_name like :query limit " . $max_results;
			}
			$stmt = $db->prepare($getTracks);
			$stmt->bindValue(':query', '%' . $artist . '%');
		}

		$tracks = $stmt->execute();

	}


	catch (PDOException $e) {
		handleDbIssuePdoXml($db);
		return;
	}

	// display all artists
	$noresult = true;
	while ($track = $stmt->fetch()) {
		$noresult         = false;
		$nb_artist_tracks = getNumberOfTracksForArtist($db, $track[0]);
		if (checkIfResultAlreadyThere($w->results(), "👤 " . ucfirst($track[0]) . ' (' . $nb_artist_tracks . ' tracks)') == false) {
			$uri = $track[2];
			// in case of local track, pass track uri instead
			if($uri == '') {
				$uri = $track[3];
			}

			$w->result(null, '', "👤 " . ucfirst($track[0]) . ' (' . $nb_artist_tracks . ' tracks)', "Browse this artist", $track[1], 'no', null, "Artist▹" . $uri . '∙' . $track[0] . "▹");
		}
	}

	if ($noresult) {
		$w->result(null, 'help', "There is no result for your search", "", './images/warning.png', 'no', null, '');
		if (! $use_mopidy) {
			$w->result(null, serialize(array(
						'' /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'artist:' . $artist /* spotify_command */ ,
						'' /* query */ ,
						'' /* other_settings*/ ,
						'' /* other_action */ ,

						'' /* artist_name */ ,
						'' /* track_name */ ,
						'' /* album_name */ ,
						'' /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						'' /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), "Search for artist " . $artist . " in Spotify", array(
					'This will start a new search in Spotify',
					'alt' => 'Not Available',
					'cmd' => 'Not Available',
					'shift' => 'Not Available',
					'fn' => 'Not Available',
					'ctrl' => 'Not Available'
				), './images/spotify.png', 'yes', null, '');
		}
	}
}


/**
 * firstDelimiterAlbums function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterAlbums($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$all_playlists             = $settings->all_playlists;
	$is_alfred_playlist_active = $settings->is_alfred_playlist_active;
	$radio_number_tracks       = $settings->radio_number_tracks;
	$now_playing_notifications = $settings->now_playing_notifications;
	$max_results               = $settings->max_results;
	$alfred_playlist_uri       = $settings->alfred_playlist_uri;
	$alfred_playlist_name      = $settings->alfred_playlist_name;
	$country_code              = $settings->country_code;
	$last_check_update_time    = $settings->last_check_update_time;
	$oauth_client_id           = $settings->oauth_client_id;
	$oauth_client_secret       = $settings->oauth_client_secret;
	$oauth_redirect_uri        = $settings->oauth_redirect_uri;
	$oauth_access_token        = $settings->oauth_access_token;
	$oauth_expires             = $settings->oauth_expires;
	$oauth_refresh_token       = $settings->oauth_refresh_token;
	$display_name              = $settings->display_name;
	$userid                    = $settings->userid;
	$echonest_api_key          = $settings->echonest_api_key;

	// New Releases menu
	$w->result(null, '', 'New Releases', 'Browse new album releases', './images/new_releases.png', 'no', null, 'New Releases▹');

	//
	// Search albums
	//
	$album = $words[1];
	try {
		if (mb_strlen($album) < 2) {
			if ($all_playlists == false) {
				$getTracks = "select album_name,album_artwork_path,artist_name,album_uri,album_type from tracks where yourmusic=1" . "  group by album_name order by max(added_at) desc limit " . $max_results;
			} else {
				$getTracks = "select album_name,album_artwork_path,artist_name,album_uri,album_type from tracks group by album_name order by max(added_at) desc limit " . $max_results;
			}
			$stmt = $db->prepare($getTracks);
		} else {
			if ($all_playlists == false) {
				$getTracks = "select album_name,album_artwork_path,artist_name,album_uri,album_type from tracks where yourmusic=1 and album_name like :query group by album_name order by max(added_at) desc limit " . $max_results;
			} else {
				$getTracks = "select album_name,album_artwork_path,artist_name,album_uri,album_type from tracks where album_name like :query group by album_name order by max(added_at) desc limit " . $max_results;
			}
			$stmt = $db->prepare($getTracks);
			$stmt->bindValue(':query', '%' . $album . '%');
		}

		$tracks = $stmt->execute();
	}
	catch (PDOException $e) {
		handleDbIssuePdoXml($db);
		return;
	}

	// display all albums
	$noresult = true;
	while ($track = $stmt->fetch()) {
		$noresult        = false;
		$nb_album_tracks = getNumberOfTracksForAlbum($db, $track[3]);
		if (checkIfResultAlreadyThere($w->results(), ucfirst($track[0]) . ' (' . $nb_album_tracks . ' tracks)') == false) {
			$w->result(null, '', ucfirst($track[0]) . ' (' . $nb_album_tracks . ' tracks)', $track[4] . ' by ' . $track[2], $track[1], 'no', null, "Album▹" . $track[3] . '∙' . $track[0] . "▹");
		}
	}

	if ($noresult) {
		$w->result(null, 'help', "There is no result for your search", "", './images/warning.png', 'no', null, '');
		if (! $use_mopidy) {
			$w->result(null, serialize(array(
						'' /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'album:' . $album /* spotify_command */ ,
						'' /* query */ ,
						'' /* other_settings*/ ,
						'' /* other_action */ ,
						'' /* artist_name */ ,
						'' /* track_name */ ,
						'' /* album_name */ ,
						'' /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						'' /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), "Search for album " . $album . " in Spotify", array(
					'This will start a new search in Spotify',
					'alt' => 'Not Available',
					'cmd' => 'Not Available',
					'shift' => 'Not Available',
					'fn' => 'Not Available',
					'ctrl' => 'Not Available'
				), './images/spotify.png', 'yes', null, '');
		}
	}
}


/**
 * firstDelimiterFeaturedPlaylist function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterFeaturedPlaylist($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$country_code = $settings->country_code;

	$w->result(null, '', getCountryName($country_code), 'Browse the current featured playlists in ' . getCountryName($country_code), './images/star.png', 'no', null, 'Featured Playlist▹' . $country_code . '▹');

	if ($country_code != 'US') {
		$w->result(null, '', getCountryName('US'), 'Browse the current featured playlists in ' . getCountryName('US'), './images/star.png', 'no', null, 'Featured Playlist▹US▹');
	}

	if ($country_code != 'GB') {
		$w->result(null, '', getCountryName('GB'), 'Browse the current featured playlists in ' . getCountryName('GB'), './images/star.png', 'no', null, 'Featured Playlist▹GB▹');
	}

	$w->result(null, '', 'Choose Another country', 'Browse the current featured playlists in another country of your choice', './images/star.png', 'no', null, 'Featured Playlist▹Choose a Country▹');
}


/**
 * firstDelimiterSearchOnline function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterSearchOnline($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$all_playlists             = $settings->all_playlists;
	$is_alfred_playlist_active = $settings->is_alfred_playlist_active;
	$radio_number_tracks       = $settings->radio_number_tracks;
	$now_playing_notifications = $settings->now_playing_notifications;
	$max_results               = $settings->max_results;
	$alfred_playlist_uri       = $settings->alfred_playlist_uri;
	$alfred_playlist_name      = $settings->alfred_playlist_name;
	$country_code              = $settings->country_code;
	$last_check_update_time    = $settings->last_check_update_time;
	$oauth_client_id           = $settings->oauth_client_id;
	$oauth_client_secret       = $settings->oauth_client_secret;
	$oauth_redirect_uri        = $settings->oauth_redirect_uri;
	$oauth_access_token        = $settings->oauth_access_token;
	$oauth_expires             = $settings->oauth_expires;
	$oauth_refresh_token       = $settings->oauth_refresh_token;
	$display_name              = $settings->display_name;
	$userid                    = $settings->userid;
	$echonest_api_key          = $settings->echonest_api_key;

	//
	// Search online
	//
	$the_query = $words[1] . "*";

	if (mb_strlen($the_query) < 2) {

		if ($kind == "Search Online") {

			$w->result(null, 'help', "Search for playlists, artists, albums or tracks online, i.e not in your library", "Begin typing at least 3 characters to start search online. This is using slow Spotify API, be patient.", './images/info.png', 'no', null, '');

			$w->result(null, null, "Search for playlists only", array(
					'This will search for playlists online, i.e not in your library',
					'alt' => 'Not Available',
					'cmd' => 'Not Available',
					'shift' => 'Not Available',
					'fn' => 'Not Available',
					'ctrl' => 'Not Available'
				), './images/playlists.png', 'no', null, 'Search Playlists Online▹');

			$w->result(null, null, "Search for tracks only", array(
					'This will search for tracks online, i.e not in your library',
					'alt' => 'Not Available',
					'cmd' => 'Not Available',
					'shift' => 'Not Available',
					'fn' => 'Not Available',
					'ctrl' => 'Not Available'
				), './images/tracks.png', 'no', null, 'Search Tracks Online▹');

			$w->result(null, null, "Search for artists only", array(
					'This will search for artists online, i.e not in your library',
					'alt' => 'Not Available',
					'cmd' => 'Not Available',
					'shift' => 'Not Available',
					'fn' => 'Not Available',
					'ctrl' => 'Not Available'
				), './images/artists.png', 'no', null, 'Search Artists Online▹');

			$w->result(null, null, "Search for albums only", array(
					'This will search for albums online, i.e not in your library',
					'alt' => 'Not Available',
					'cmd' => 'Not Available',
					'shift' => 'Not Available',
					'fn' => 'Not Available',
					'ctrl' => 'Not Available'
				), './images/albums.png', 'no', null, 'Search Albums Online▹');
		} elseif ($kind == "Search Playlists Online") {
			$w->result(null, 'help', "Search playlists online, i.e not in your library", "Begin typing at least 3 characters to start search online. This is using slow Spotify API, be patient.", './images/info.png', 'no', null, '');
		} elseif ($kind == "Search Artists Online") {
			$w->result(null, 'help', "Search artists online, i.e not in your library", "Begin typing at least 3 characters to start search online. This is using slow Spotify API, be patient.", './images/info.png', 'no', null, '');
		} elseif ($kind == "Search Tracks Online") {
			$w->result(null, 'help', "Search tracks online, i.e not in your library", "Begin typing at least 3 characters to start search online. This is using slow Spotify API, be patient.", './images/info.png', 'no', null, '');
		} elseif ($kind == "Search Albums Online") {
			$w->result(null, 'help', "Search albums online, i.e not in your library", "Begin typing at least 3 characters to start search online. This is using slow Spotify API, be patient.", './images/info.png', 'no', null, '');
		}
	} else {

		$search_playlists = false;
		$search_artists   = false;
		$search_albums    = false;
		$search_tracks    = false;

		if ($kind == "Search Online") {
			$search_playlists       = true;
			$search_artists         = true;
			$search_albums          = true;
			$search_tracks          = true;
			$search_playlists_limit = 8;
			$search_artists_limit   = 5;
			$search_albums_limit    = 5;
			$search_tracks_limit    = 20;
		} elseif ($kind == "Search Playlists Online") {
			$search_playlists       = true;
			$search_playlists_limit = ($max_results <= 50) ? $max_results : 50;
		} elseif ($kind == "Search Artists Online") {
			$search_artists       = true;
			$search_artists_limit = ($max_results <= 50) ? $max_results : 50;
		} elseif ($kind == "Search Albums Online") {
			$search_albums       = true;
			$search_albums_limit = ($max_results <= 50) ? $max_results : 50;
		} elseif ($kind == "Search Tracks Online") {
			$search_tracks       = true;
			$search_tracks_limit = ($max_results <= 50) ? $max_results : 50;
		}

		$noresult = true;

		if ($search_artists == true) {
			// Search Artists
			//

			// call to web api, if it fails,
			// it displays an error in main window
			$query   = 'artist:' . strtolower($the_query);
			$results = searchWebApi($w, $country_code, $query, 'artist', $search_artists_limit, false);

			foreach ($results as $artist) {
				if (checkIfResultAlreadyThere($w->results(), "👤 " . escapeQuery(ucfirst($artist->name))) == false) {
					$noresult = false;
					$w->result(null, '', "👤 " . escapeQuery(ucfirst($artist->name)), "Browse this artist", getArtistArtwork($w, $artist->uri, $artist->name, false), 'no', null, "Online▹" . $artist->uri . '@' . escapeQuery($artist->name) . '▹');
				}
			}
		}

		if ($search_albums == true) {
			// Search Albums
			//
			// call to web api, if it fails,
			// it displays an error in main window
			$query   = 'album:' . strtolower($the_query);
			$results = searchWebApi($w, $country_code, $query, 'album', $search_albums_limit, false);

			try {
				$api = getSpotifyWebAPI($w);
			}
			catch (SpotifyWebAPI\SpotifyWebAPIException $e) {
				$w->result(null, 'help', "Exception occurred", "" . $e->getMessage(), './images/warning.png', 'no', null, '');
				echo $w->tojson();
				exit;
			}

			foreach ($results as $album) {
				if (checkIfResultAlreadyThere($w->results(), escapeQuery(ucfirst($album->name))) == false) {
					$noresult = false;

					try {
						$full_album = $api->getAlbum($album->id);
					}
					catch (SpotifyWebAPI\SpotifyWebAPIException $e) {
						$w->result(null, 'help', "Exception occurred", "" . $e->getMessage(), './images/warning.png', 'no', null, '');
						echo $w->tojson();
						exit;
					}
					$w->result(null, '', escapeQuery(ucfirst($album->name)) . ' (' . $full_album->tracks->total . ' tracks)', $album->album_type . ' by ' . escapeQuery($full_album->artists[0]->name), getTrackOrAlbumArtwork($w, $album->uri, false), 'no', null, 'Online▹' . $full_album->artists[0]->uri . '@' . escapeQuery($full_album->artists[0]->name) . '@' . $album->uri . '@' . escapeQuery($album->name) . '▹');
				}
			}
		}

		if ($search_playlists == true) {
			// Search Playlists
			//

			// call to web api, if it fails,
			// it displays an error in main window
			$query   = 'playlist:' . strtolower($the_query);
			$results = searchWebApi($w, $country_code, $query, 'playlist', $search_playlists_limit, false);

			foreach ($results as $playlist) {
				$noresult = false;
				$w->result(null, '', "🎵" . escapeQuery($playlist->name), "by " . $playlist->owner->id . " ● " . $playlist->tracks->total . " tracks", getPlaylistArtwork($w, $playlist->uri, false), 'no', null, "Online Playlist▹" . $playlist->uri . '∙' . escapeQuery($playlist->name) . '▹');

			}
		}

		if ($search_tracks == true) {
			// Search Tracks
			//
			// call to web api, if it fails,
			// it displays an error in main window
			$query   = 'track:' . strtolower($the_query);
			$results = searchWebApi($w, $country_code, $query, 'track', $search_tracks_limit, false);
			$first = true;
			foreach ($results as $track) {
				// if ($first == true) {
				//     $subtitle = "⌥ (play album) ⌘ (play artist) ctrl (lookup online)";
				//     $subtitle = "$subtitle fn (add track to ...) ⇧ (add album to ...)";
				//     $w->result(null, 'help', "Select a track below to play it (or choose alternative described below)", $subtitle, './images/info.png', 'no', null, '');
				// }
				// $first         = false;
				$noresult      = false;
				$track_artwork = getTrackOrAlbumArtwork($w, $track->uri, false);

				$artists = $track->artists;
				$artist  = $artists[0];
				$album   = $track->album;

				$w->result(null, serialize(array(
							$track->uri /*track_uri*/ ,
							$album->uri /* album_uri */ ,
							$artist->uri /* artist_uri */ ,
							'' /* playlist_uri */ ,
							'' /* spotify_command */ ,
							'' /* query */ ,
							'' /* other_settings*/ ,
							'play_track_in_album_context' /* other_action */ ,
							escapeQuery($artist->name) /* artist_name */ ,
							escapeQuery($track->name) /* track_name */ ,
							escapeQuery($album->name) /* album_name */ ,
							$track_artwork /* track_artwork_path */ ,
							'' /* artist_artwork_path */ ,
							'' /* album_artwork_path */ ,
							'' /* playlist_name */ ,
							'' /* playlist_artwork_path */
						)), escapeQuery(ucfirst($artist->name)) . " ● " . escapeQuery($track->name), array(
						beautifyTime($track->duration_ms / 1000) . " ● " . escapeQuery($album->name),
						'alt' => 'Play album ' . escapeQuery($album->name) . ' in Spotify',
						'cmd' => 'Play artist ' . escapeQuery($artist->name) . ' in Spotify',
						'fn' => 'Add track ' . escapeQuery($track->name) . ' to ...',
						'shift' => 'Add album ' . escapeQuery($album->name) . ' to ...',
						'ctrl' => 'Search artist ' . escapeQuery($artist->name) . ' online'
					), $track_artwork, 'yes', null, '');
			}
		}

		if ($noresult) {
			$w->result(null, 'help', "There is no result for this search", "", './images/warning.png', 'no', null, '');
		}
	}
}


/**
 * firstDelimiterNewReleases function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterNewReleases($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$country_code = $settings->country_code;

	$w->result(null, '', getCountryName($country_code), 'Browse the new album releases in ' . getCountryName($country_code), './images/new_releases.png', 'no', null, 'New Releases▹' . $country_code . '▹');

	if ($country_code != 'US') {
		$w->result(null, '', getCountryName('US'), 'Browse the new album releases in ' . getCountryName('US'), './images/new_releases.png', 'no', null, 'New Releases▹US▹');
	}

	if ($country_code != 'GB') {
		$w->result(null, '', getCountryName('GB'), 'Browse the new album releases in ' . getCountryName('GB'), './images/new_releases.png', 'no', null, 'New Releases▹GB▹');
	}

	$w->result(null, '', 'Choose Another country', 'Browse the new album releases in another country of your choice', './images/new_releases.png', 'no', null, 'New Releases▹Choose a Country▹');
}


/**
 * firstDelimiterCurrentTrack function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterCurrentTrack($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$all_playlists             = $settings->all_playlists;
	$is_alfred_playlist_active = $settings->is_alfred_playlist_active;
	$radio_number_tracks       = $settings->radio_number_tracks;
	$now_playing_notifications = $settings->now_playing_notifications;
	$max_results               = $settings->max_results;
	$alfred_playlist_uri       = $settings->alfred_playlist_uri;
	$alfred_playlist_name      = $settings->alfred_playlist_name;
	$country_code              = $settings->country_code;
	$last_check_update_time    = $settings->last_check_update_time;
	$oauth_client_id           = $settings->oauth_client_id;
	$oauth_client_secret       = $settings->oauth_client_secret;
	$oauth_redirect_uri        = $settings->oauth_redirect_uri;
	$oauth_access_token        = $settings->oauth_access_token;
	$oauth_expires             = $settings->oauth_expires;
	$oauth_refresh_token       = $settings->oauth_refresh_token;
	$display_name              = $settings->display_name;
	$userid                    = $settings->userid;
	$echonest_api_key          = $settings->echonest_api_key;
	$is_public_playlists       = $settings->is_public_playlists;
	$use_mopidy                = $settings->use_mopidy;
	$is_display_rating         = $settings->is_display_rating;

	if ($use_mopidy) {
		$retArr = array(getCurrentTrackInfoWithMopidy($w));
	} else {
		// get info on current song
		exec("./src/track_info.ksh 2>&1", $retArr, $retVal);
		if ($retVal != 0) {
			$w->result(null, 'help', "AppleScript execution failed!", "Message: " . htmlspecialchars($retArr[0]), './images/warning.png', 'no', null, '');
			$w->result(null, serialize(array(
						'' /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'' /* spotify_command */ ,
						'' /* query */ ,
						'Open▹' . 'http://alfred-spotify-mini-player.com/blog/issue-with-latest-spotify-update/' /* other_settings*/ ,
						'' /* other_action */ ,
						'' /* artist_name */ ,
						'' /* track_name */ ,
						'' /* album_name */ ,
						'' /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						'' /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), 'Maybe you have an issue with a Broken Spotify version?', "Go to the article to get more information", './images/website.png', 'yes', null, '');
			return;
		}
	}

	if (isset($retArr[0]) && substr_count($retArr[0], '▹') > 0) {
		$results = explode('▹', $retArr[0]);
		if ($results[1] == '' || $results[2] == '') {
			$w->result(null, 'help', "Current track is not valid: Artist or Album name is missing", "Fill missing information in Spotify and retry again", './images/warning.png', 'no', null, '');
			echo $w->tojson();
			exit;
		}

		$href = explode(':', $results[4] );
		$added = '';
		if ($href[1] == 'local') {
			$added = '📌 ';
		}
		$subtitle             = "⌥ (play album) ⌘ (play artist) ctrl (lookup online)";
		$subtitle             = "$subtitle fn (add track to ...) ⇧ (add album to ...)";
		if ($results[3] == "playing") {
			$w->result(null, serialize(array(
						$results[4] /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'' /* spotify_command */ ,
						'' /* query */ ,
						'' /* other_settings*/ ,
						'pause' /* other_action */ ,
						escapeQuery($results[1]) /* artist_name */ ,
						escapeQuery($results[0]) /* track_name */ ,
						escapeQuery($results[2]) /* album_name */ ,
						'' /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						'' /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), $added . escapeQuery($results[0]) . " ● " . escapeQuery($results[1]) . " ● " . escapeQuery($results[2]) . " ● " . floatToStars(($results[6] / 100) ? $is_display_rating : 0) . ' ' . beautifyTime($results[5]/ 1000), array(
					$subtitle,
					'alt' => 'Play album ' . escapeQuery($results[2]) . ' in Spotify',
					'cmd' => 'Play artist ' . escapeQuery($results[1]) . ' in Spotify',
					'fn' => 'Add track ' . escapeQuery($results[0]) . ' to ...',
					'shift' => 'Add album ' . escapeQuery($results[2]) . ' to ...',
					'ctrl' => 'Search artist ' . escapeQuery($results[1]) . ' online'
				), ($results[3] == "playing") ? './images/pause.png' : './images/play.png', 'yes', null, '');

		} else {
			$w->result(null, serialize(array(
						$results[4] /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'' /* spotify_command */ ,
						'' /* query */ ,
						'' /* other_settings*/ ,
						'play' /* other_action */ ,
						escapeQuery($results[1]) /* artist_name */ ,
						escapeQuery($results[0]) /* track_name */ ,
						escapeQuery($results[2]) /* album_name */ ,
						'' /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						'' /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), $added . escapeQuery($results[0]) . " ● " . escapeQuery($results[1]) . " ● " . escapeQuery($results[2]) . " ● " . floatToStars($results[6] / 100) . ' (' . beautifyTime($results[5]) . ')', array(
					$subtitle,
					'alt' => 'Play album ' . escapeQuery($results[2]) . ' in Spotify',
					'cmd' => 'Play artist ' . escapeQuery($results[1]) . ' in Spotify',
					'fn' => 'Add track ' . escapeQuery($results[0]) . ' to ...',
					'shift' => 'Add album ' . escapeQuery($results[2]) . ' to ...',
					'ctrl' => 'Search artist ' . escapeQuery($results[1]) . ' online'
				), ($results[3] == "playing") ? './images/pause.png' : './images/play.png', 'yes', null, '');
		}


		$getTracks = "select artist_name,artist_uri from tracks where artist_name=:artist_name limit " . 1;

		try {
			$stmt = $db->prepare($getTracks);
			$stmt->bindValue(':artist_name', escapeQuery($results[1]));
			$tracks = $stmt->execute();

		}
		catch (PDOException $e) {
			handleDbIssuePdoXml($db);
			return;
		}

		// check if artist is in library
		$noresult = true;
		while ($track = $stmt->fetch()) {
			if ($track[1] != '') {
				$artist_uri = $track[1];
				$noresult   = false;
			}
		}
		if ($noresult == false) {
			$w->result(null, '', "👤 " . ucfirst(escapeQuery($results[1])), "Browse this artist", getArtistArtwork($w, $artist_uri, $results[1], false), 'no', null, "Artist▹" . $artist_uri . '∙' . escapeQuery($results[1]) . "▹");
		} else {
			// artist is not in library
			$w->result(null, '', "👤 " . ucfirst(escapeQuery($results[1])), "Browse this artist", getArtistArtwork($w, '' /* empty artist_uri */, $results[1], false), 'no', null, "Artist▹" . $results[4] . '∙' . escapeQuery($results[1]) . "▹");
		}

		// use track uri here
		$album_artwork_path = getTrackOrAlbumArtwork($w, $results[4], false);
		$w->result(null, serialize(array(
					$results[4] /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'playalbum' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					escapeQuery($results[2]) /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					$album_artwork_path /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "💿 " . escapeQuery($results[2]), 'Play album', $album_artwork_path, 'yes', null, '');

		// use track uri here
		$w->result(null, '', "💿 " . ucfirst(escapeQuery($results[2])), '☁︎ Query all tracks from this album online..', './images/online_album.png', 'no', null, "Online▹" . $results[4] . '@' . escapeQuery($results[1]) . '@' . $results[4] . '@' . escapeQuery($results[2]) . '▹');


		$w->result(null, '', "Get Lyrics for track " . escapeQuery($results[0]), "This will fetch lyrics online", './images/lyrics.png', 'no', null, "Lyrics▹" . $results[4] . "∙" . escapeQuery($results[1]) . '∙' . escapeQuery($results[0]));

		if ($update_in_progress == false) {
			$w->result(null, '', 'Add track ' . escapeQuery($results[0]) . ' to...', 'This will add current track to Your Music or a playlist you will choose in next step', './images/add.png', 'no', null, 'Add▹' . $results[4] . '∙' . escapeQuery($results[0]) . '▹');

			$w->result(null, '', 'Remove track ' . escapeQuery($results[0]) . ' from...', 'This will remove current track from Your Music or a playlist you will choose in next step', './images/remove.png', 'no', null, 'Remove▹' . $results[4] . '∙' . escapeQuery($results[0]) . '▹');

			$privacy_status = 'private';
			if ($is_public_playlists) {
				$privacy_status = 'public';
			}
			$w->result(null, serialize(array(
						'' /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'' /* spotify_command */ ,
						'' /* query */ ,
						'' /* other_settings*/ ,
						'current_track_radio' /* other_action */ ,
						'' /* artist_name */ ,
						'' /* track_name */ ,
						'' /* album_name */ ,
						'' /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						'' /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), "Create a Song Radio Playlist based on " . escapeQuery($results[0]), array(
					'This will create a ' . $privacy_status . ' song radio playlist with ' . $radio_number_tracks . ' tracks for the current track',
					'alt' => 'Not Available',
					'cmd' => 'Not Available',
					'shift' => 'Not Available',
					'fn' => 'Not Available',
					'ctrl' => 'Not Available'
				), './images/radio_song.png', 'yes', null, '');
		}

		if ($all_playlists == true) {
			$getTracks = "select playlist_uri from tracks where uri=:uri limit " . $max_results;
			try {
				$stmtgetTracks = $db->prepare($getTracks);
				$stmtgetTracks->bindValue(':uri', $results[4]);
				$stmtgetTracks->execute();
			}
			catch (PDOException $e) {
				handleDbIssuePdoXml($db);
				return;
			}

			while ($track = $stmtgetTracks->fetch()) {

				if ($track[0] == '') {
					// The track is in Your Music
					$w->result(null, '', 'In "Your Music"', "The track is in Your Music", './images/yourmusic.png', 'no', null, "Your Music▹Tracks▹" . escapeQuery($results[0]));
				} else {
					$getPlaylists = "select uri,name,nb_tracks,author,username,playlist_artwork_path,ownedbyuser,nb_playable_tracks,duration_playlist,collaborative,public from playlists where uri=:uri";

					try {
						$stmtGetPlaylists = $db->prepare($getPlaylists);
						$stmtGetPlaylists->bindValue(':uri', $track[0]);
						$playlists = $stmtGetPlaylists->execute();
					}
					catch (PDOException $e) {
						handleDbIssuePdoXml($db);
						return;
					}

					while ($playlist = $stmtGetPlaylists->fetch()) {
						$added = ' ';
						if (startswith($playlist[1], 'Artist radio for')) {
							$added = '📻 ';
						}
						if (checkIfResultAlreadyThere($w->results(), "🎵" . $added . "In playlist " . $playlist[1]) == false) {
							if ($playlist[9]) {
								$public_status = 'collaborative';
							} else {
								if ($playlist[10]) {
									$public_status = 'public';
								} else {
									$public_status = 'private';
								}
							}
							$w->result(null, '', "🎵" . $added . "In playlist " . $playlist[1], $public_status . " playlist by " . $playlist[3] . " ● " . $playlist[7] . " tracks ● " . $playlist[8], $playlist[5], 'no', null, "Playlist▹" . $playlist[0] . "▹");
						}
					}
				}
			}
		}
	} else {
		$w->result(null, 'help', "There is no track currently playing", "Launch a track and come back here", './images/warning.png', 'no', null, '');
	}

}


/**
 * firstDelimiterYourMusic function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterYourMusic($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$all_playlists             = $settings->all_playlists;
	$is_alfred_playlist_active = $settings->is_alfred_playlist_active;
	$radio_number_tracks       = $settings->radio_number_tracks;
	$now_playing_notifications = $settings->now_playing_notifications;
	$max_results               = $settings->max_results;
	$alfred_playlist_uri       = $settings->alfred_playlist_uri;
	$alfred_playlist_name      = $settings->alfred_playlist_name;
	$country_code              = $settings->country_code;
	$last_check_update_time    = $settings->last_check_update_time;
	$oauth_client_id           = $settings->oauth_client_id;
	$oauth_client_secret       = $settings->oauth_client_secret;
	$oauth_redirect_uri        = $settings->oauth_redirect_uri;
	$oauth_access_token        = $settings->oauth_access_token;
	$oauth_expires             = $settings->oauth_expires;
	$oauth_refresh_token       = $settings->oauth_refresh_token;
	$display_name              = $settings->display_name;
	$userid                    = $settings->userid;
	$echonest_api_key          = $settings->echonest_api_key;

	$thequery = $words[1];

	if (mb_strlen($thequery) < 2) {
		$getCounters = 'select * from counters';
		try {
			$stmt = $db->prepare($getCounters);

			$counters = $stmt->execute();
			$counter  = $stmt->fetch();

		}
		catch (PDOException $e) {
			handleDbIssuePdoXml($db);
			return;
		}

		$all_tracks        = $counter[0];
		$yourmusic_tracks  = $counter[1];
		$all_artists       = $counter[2];
		$yourmusic_artists = $counter[3];
		$all_albums        = $counter[4];
		$yourmusic_albums  = $counter[5];
		$nb_playlists      = $counter[6];

		$w->result(null, '', 'Tracks', 'Browse your ' . $yourmusic_tracks . ' tracks in Your Music', './images/tracks.png', 'no', null, 'Your Music▹Tracks▹');
		$w->result(null, '', 'Albums', 'Browse your ' . $yourmusic_albums . ' albums in Your Music', './images/albums.png', 'no', null, 'Your Music▹Albums▹');
		$w->result(null, '', 'Artists', 'Browse your ' . $yourmusic_artists . ' artists in Your Music', './images/artists.png', 'no', null, 'Your Music▹Artists▹');

	} else {
		//
		// Search artists
		//
		$getTracks = "select artist_name,artist_uri,artist_artwork_path from tracks where yourmusic=1 and artist_name like :artist_name limit " . $max_results;

		try {
			$stmt = $db->prepare($getTracks);
			$stmt->bindValue(':artist_name', '%' . $thequery . '%');

			$tracks = $stmt->execute();

		}
		catch (PDOException $e) {
			handleDbIssuePdoXml($db);
			return;
		}
		$noresult = true;
		while ($track = $stmt->fetch()) {

			if (checkIfResultAlreadyThere($w->results(), "👤 " . ucfirst($track[0])) == false) {
				$noresult = false;
				$w->result(null, '', "👤 " . ucfirst($track[0]), "Browse this artist", $track[2], 'no', null, "Artist▹" . $track[1] . '∙' . $track[0] . "▹");
			}
		}

		//
		// Search everything
		//
		$getTracks = "select yourmusic, popularity, uri, album_uri, artist_uri, track_name, album_name, artist_name, album_type, track_artwork_path, artist_artwork_path, album_artwork_path, playlist_name, playlist_uri, playable, added_at, duration, nb_times_played, local_track from tracks where yourmusic=1 and (artist_name like :query or album_name like :query or track_name like :query)" . " limit " . $max_results;

		try {
			$stmt = $db->prepare($getTracks);
			$stmt->bindValue(':query', '%' . $thequery . '%');

			$tracks = $stmt->execute();

		}
		catch (PDOException $e) {
			handleDbIssuePdoXml($db);
			return;
		}

		while ($track = $stmt->fetch()) {
			// if ($noresult == true) {
			//     $subtitle = "⌥ (play album) ⌘ (play artist) ctrl (lookup online)";
			//     $subtitle = "$subtitle fn (add track to ...) ⇧ (add album to ...)";
			//     $w->result(null, 'help', "Select a track below to play it (or choose alternative described below)", $subtitle, './images/info.png', 'no', null, '');
			// }
			$noresult = false;
			$subtitle = $track[6];

			if (checkIfResultAlreadyThere($w->results(), ucfirst($track[7]) . " ● " . $track[5]) == false) {

				$w->result(null, serialize(array(
							$track[2] /*track_uri*/ ,
							$track[3] /* album_uri */ ,
							$track[4] /* artist_uri */ ,
							'' /* playlist_uri */ ,
							'' /* spotify_command */ ,
							'' /* query */ ,
							'' /* other_settings*/ ,
							'' /* other_action */ ,
							$track[7] /* artist_name */ ,
							$track[5] /* track_name */ ,
							$track[6] /* album_name */ ,
							$track[9] /* track_artwork_path */ ,
							$track[10] /* artist_artwork_path */ ,
							$track[11] /* album_artwork_path */ ,
							'' /* playlist_name */ ,
							'' /* playlist_artwork_path */
						)), ucfirst($track[7]) . " ● " . $track[5], $arrayresult = array(
						$track[16] . " ● " . $subtitle . getPlaylistsForTrack($db, $track[2]),
						'alt' => 'Play album ' . $track[6] . ' in Spotify',
						'cmd' => 'Play artist ' . $track[7] . ' in Spotify',
						'fn' => 'Add track ' . $track[5] . ' to ...',
						'shift' => 'Add album ' . $track[6] . ' to ...',
						'ctrl' => 'Search artist ' . $track[7] . ' online'
					), $track[9], 'yes', array(
						'copy' => ucfirst($track[7]) . " ● " . $track[5],
						'largetype' => ucfirst($track[7]) . " ● " . $track[5]
					), '');

			}
		}

		if ($noresult) {
			$w->result(null, 'help', "There is no result for your search", "", './images/warning.png', 'no', null, '');
		}
	}
}


/**
 * firstDelimiterLyrics function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterLyrics($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$all_playlists             = $settings->all_playlists;
	$is_alfred_playlist_active = $settings->is_alfred_playlist_active;
	$radio_number_tracks       = $settings->radio_number_tracks;
	$now_playing_notifications = $settings->now_playing_notifications;
	$max_results               = $settings->max_results;
	$alfred_playlist_uri       = $settings->alfred_playlist_uri;
	$alfred_playlist_name      = $settings->alfred_playlist_name;
	$country_code              = $settings->country_code;
	$last_check_update_time    = $settings->last_check_update_time;
	$oauth_client_id           = $settings->oauth_client_id;
	$oauth_client_secret       = $settings->oauth_client_secret;
	$oauth_redirect_uri        = $settings->oauth_redirect_uri;
	$oauth_access_token        = $settings->oauth_access_token;
	$oauth_expires             = $settings->oauth_expires;
	$oauth_refresh_token       = $settings->oauth_refresh_token;
	$display_name              = $settings->display_name;
	$userid                    = $settings->userid;
	$echonest_api_key          = $settings->echonest_api_key;

	if (substr_count($query, '∙') == 2) {
		//
		// Search Lyrics
		//
		$tmp         = $words[1];
		$words       = explode('∙', $tmp);
		$track_uri   = $words[0];
		$artist_name = $words[1];
		$track_name  = $words[2];

		list($lyrics_url, $lyrics) = getLyrics($w, $artist_name, $track_name);
		if ($userid != 'vdesabou') {
			stathat_ez_count('AlfredSpotifyMiniPlayer', 'lyrics', 1);
		}
		if ($lyrics_url != false) {
			$w->result(null, serialize(array(
						'' /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'' /* spotify_command */ ,
						'' /* query */ ,
						'Open▹' . $lyrics_url /* other_settings*/ ,
						'' /* other_action */ ,
						'' /* artist_name */ ,
						'' /* track_name */ ,
						'' /* album_name */ ,
						'' /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						'' /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), 'See lyrics for ' . $track_name . ' by ' . $artist_name . ' online', "This will open your default browser", './images/lyrics.png', 'yes', null, '');

			$track_artwork = getTrackOrAlbumArtwork($w, $track_uri, false);

			$wrapped          = wordwrap($lyrics, 70, "\n", false);
			$lyrics_sentances = explode("\n", $wrapped);

			for ($i = 0; $i < count($lyrics_sentances); $i++) {
				$w->result(null, '', $lyrics_sentances[$i], '', $track_artwork, 'no', null, '');
			}
		} else {
			$w->result(null, 'help', "No lyrics found!", "", './images/warning.png', 'no', null, '');
			echo $w->tojson();
			exit;
		}
	}
}


/**
 * firstDelimiterSettings function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterSettings($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$all_playlists              = $settings->all_playlists;
	$is_alfred_playlist_active  = $settings->is_alfred_playlist_active;
	$radio_number_tracks        = $settings->radio_number_tracks;
	$now_playing_notifications  = $settings->now_playing_notifications;
	$max_results                = $settings->max_results;
	$alfred_playlist_uri        = $settings->alfred_playlist_uri;
	$alfred_playlist_name       = $settings->alfred_playlist_name;
	$country_code               = $settings->country_code;
	$last_check_update_time     = $settings->last_check_update_time;
	$oauth_client_id            = $settings->oauth_client_id;
	$oauth_client_secret        = $settings->oauth_client_secret;
	$oauth_redirect_uri         = $settings->oauth_redirect_uri;
	$oauth_access_token         = $settings->oauth_access_token;
	$oauth_expires              = $settings->oauth_expires;
	$oauth_refresh_token        = $settings->oauth_refresh_token;
	$display_name               = $settings->display_name;
	$userid                     = $settings->userid;
	$echonest_api_key           = $settings->echonest_api_key;
	$is_public_playlists        = $settings->is_public_playlists;
	$quick_mode                 = $settings->quick_mode;
	$use_mopidy                 = $settings->use_mopidy;
	$mopidy_server              = $settings->mopidy_server;
	$mopidy_port                = $settings->mopidy_port;
	$is_display_rating          = $settings->is_display_rating;
	$volume_percent             = $settings->volume_percent;
	$is_autoplay_playlist       = $settings->is_autoplay_playlist;
	$use_growl                  = $settings->use_growl;

	if ($update_in_progress == false) {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'refresh_library' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Refresh your library", array(
				'Do this when your library has changed (outside the scope of this workflow)',
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/update.png', 'yes', null, '');
	}

	if ($is_alfred_playlist_active == true) {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'disable_alfred_playlist' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Control Your Music", array(
				"You will control Your Music (if disabled, you control Alfred Playlist)",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/yourmusic.png', 'yes', null, '');
	} else {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'enable_alfred_playlist' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Control Alfred Playlist", array(
				"You will control the Alfred Playlist (if disabled, you control Your Music)",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/alfred_playlist.png', 'yes', null, '');
	}

	if ($all_playlists == true) {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'disable_all_playlist' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), 'Set Search Scope to Your Music only', array(
				'Select to search only in "Your Music"',
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/search_scope_yourmusic_only.png', 'yes', null, '');

	} else {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'enable_all_playlist' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), 'Unset Search Scope to Your Music only', array(
				'Select to search in your complete library ("Your Music" and all Playlists)',
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/search.png', 'yes', null, '');
	}
	$w->result(null, '', "Configure Max Number of Results (currently " . $max_results . ")", "Number of results displayed (it does not apply to the list of your playlists)", './images/results_numbers.png', 'no', null, 'Settings▹MaxResults▹');
	$w->result(null, '', "Configure Number of Radio tracks (currently " . $radio_number_tracks . ")", "Number of tracks when creating a Radio Playlist.", './images/radio_numbers.png', 'no', null, 'Settings▹RadioTracks▹');
	$w->result(null, '', "Configure Volume Percent (currently " . $volume_percent . "%)", "The percentage of volume which is increased or decreased.", './images/volume_up.png', 'no', null, 'Settings▹VolumePercentage▹');


	if ($now_playing_notifications == true) {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'disable_now_playing_notifications' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Disable Now Playing notifications", array(
				"Do not display notifications for current playing track",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/disable_now_playing.png', 'yes', null, '');
	} else {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'enable_now_playing_notifications' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Enable Now Playing notifications", array(
				"Display notifications for current playing track",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/enable_now_playing.png', 'yes', null, '');
	}

	if ($quick_mode == true) {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'disable_quick_mode' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Disable Quick Mode", array(
				"Do not launch directly tracks/album/artists/playlists in main search",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/disable_quick_mode.png', 'yes', null, '');
	} else {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'enable_quick_mode' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Enable Quick Mode", array(
				"Launch directly tracks/album/artists/playlists in main search",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/enable_quick_mode.png', 'yes', null, '');
	}

	if ($is_display_rating == true) {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'disable_display_rating' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Disable Track Rating", array(
				"Do not display track rating with stars in Current Track menu and notifications",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/disable_display_rating.png', 'yes', null, '');
	} else {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'enable_display_rating' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Enable Track Rating", array(
				"Display track rating with stars in Current Track menu and notifications",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/enable_display_rating.png', 'yes', null, '');
	}

	if ($is_autoplay_playlist == true) {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'disable_autoplay' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Disable Playlist Autoplay", array(
				"Do not autoplay playlists (radios and complete collection) when they are created",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/disable_autoplay.png', 'yes', null, '');
	} else {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'enable_autoplay' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Enable Playlist Autoplay", array(
				"Autoplay playlists (radios and complete collection) when they are created",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/enable_autoplay.png', 'yes', null, '');
	}

	if ($use_growl == true) {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'disable_use_growl' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Disable Growl", array(
				"Use Notification Center instead of Growl",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/disable_use_growl.png', 'yes', null, '');
	} else {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'enable_use_growl' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Enable Growl", array(
				"Use Growl instead of Notification Center",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/enable_use_growl.png', 'yes', null, '');
	}

	if ($update_in_progress == false) {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'update_library' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), 'Re-Create your library from scratch', array(
				'Do this when refresh library is not working as you would expect',
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/recreate.png', 'yes', null, '');
	}

	if ($is_public_playlists == true) {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'disable_public_playlists' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Automatically make new playlists private", array(
				"If disabled, the workflow will mark new playlists (created or followed) as private",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/disable_public_playlists.png', 'yes', null, '');
	} else {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'enable_public_playlists' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Automatically make new playlists public", array(
				"If enabled, the workflow will mark new playlists (created or followed) as public",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/enable_public_playlists.png', 'yes', null, '');
	}

	if ($use_mopidy == true) {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'disable_mopidy' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Disable Mopidy", array(
				"You will use Spotify Desktop app with AppleScript instead",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/disable_mopidy.png', 'yes', null, '');
		$w->result(null, '', "Configure Mopidy server (currently " . $mopidy_server . ")", "Server name/ip where Mopidy server is running", './images/mopidy_server.png', 'no', null, 'Settings▹MopidyServer▹');
		$w->result(null, '', "Configure Mopidy port (currently " . $mopidy_port . ")", "TCP port where Mopidy server is running", './images/mopidy_port.png', 'no', null, 'Settings▹MopidyPort▹');
	} else {
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'' /* other_settings*/ ,
					'enable_mopidy' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), "Enable Mopidy", array(
				"You will use Mopidy",
				'alt' => 'Not Available',
				'cmd' => 'Not Available',
				'shift' => 'Not Available',
				'fn' => 'Not Available',
				'ctrl' => 'Not Available'
			), './images/enable_mopidy.png', 'yes', null, '');
	}

	$w->result(null, '', 'Check for workflow update', 'Last checked: ' . beautifyTime(time() - $last_check_update_time, true) . ' ago (note this is automatically done otherwise once per day)', './images/check_update.png', 'no', null, 'Check for update...' . '▹');

	$w->result(null, serialize(array(
				'' /*track_uri*/ ,
				'' /* album_uri */ ,
				'' /* artist_uri */ ,
				'' /* playlist_uri */ ,
				'' /* spotify_command */ ,
				'' /* query */ ,
				'Open▹' . 'http://alfred-spotify-mini-player.com' /* other_settings*/ ,
				'' /* other_action */ ,
				'' /* artist_name */ ,
				'' /* track_name */ ,
				'' /* album_name */ ,
				'' /* track_artwork_path */ ,
				'' /* artist_artwork_path */ ,
				'' /* album_artwork_path */ ,
				'' /* playlist_name */ ,
				'' /* playlist_artwork_path */
			)), 'Go to the website alfred-spotify-mini-player.com', "Find out all information on the workflow on the website", './images/website.png', 'yes', null, '');
}


/**
 * firstDelimiterCheckForUpdate function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterCheckForUpdate($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$all_playlists             = $settings->all_playlists;
	$is_alfred_playlist_active = $settings->is_alfred_playlist_active;
	$radio_number_tracks       = $settings->radio_number_tracks;
	$now_playing_notifications = $settings->now_playing_notifications;
	$max_results               = $settings->max_results;
	$alfred_playlist_uri       = $settings->alfred_playlist_uri;
	$alfred_playlist_name      = $settings->alfred_playlist_name;
	$country_code              = $settings->country_code;
	$last_check_update_time    = $settings->last_check_update_time;
	$oauth_client_id           = $settings->oauth_client_id;
	$oauth_client_secret       = $settings->oauth_client_secret;
	$oauth_redirect_uri        = $settings->oauth_redirect_uri;
	$oauth_access_token        = $settings->oauth_access_token;
	$oauth_expires             = $settings->oauth_expires;
	$oauth_refresh_token       = $settings->oauth_refresh_token;
	$display_name              = $settings->display_name;
	$userid                    = $settings->userid;
	$echonest_api_key          = $settings->echonest_api_key;

	$check_results = checkForUpdate($w, 0);
	if ($check_results != null && is_array($check_results)) {
		$w->result(null, '', 'New version ' . $check_results[0] . ' is available !', $check_results[2], './images/info.png', 'no', null, '');
		$w->result(null, serialize(array(
					'' /*track_uri*/ ,
					'' /* album_uri */ ,
					'' /* artist_uri */ ,
					'' /* playlist_uri */ ,
					'' /* spotify_command */ ,
					'' /* query */ ,
					'Open▹' . $check_results[1] /* other_settings*/ ,
					'' /* other_action */ ,
					'' /* artist_name */ ,
					'' /* track_name */ ,
					'' /* album_name */ ,
					'' /* track_artwork_path */ ,
					'' /* artist_artwork_path */ ,
					'' /* album_artwork_path */ ,
					'' /* playlist_name */ ,
					'' /* playlist_artwork_path */
				)), 'Click to open and install the new version', "This will open the new version of the Spotify Mini Player workflow", './images/alfred-workflow-icon.png', 'yes', null, '');


	} elseif ($check_results == null) {
		$w->result(null, '', 'No update available', 'You are good to go!', './images/info.png', 'no', null, '');
	} else {
		$w->result(null, '', 'Error happened : ' . $check_results, 'The check for workflow update could not be done', './images/warning.png', 'no', null, '');
		if ($check_results == "This release has not been downloaded from Packal") {
			$w->result(null, serialize(array(
						'' /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'' /* spotify_command */ ,
						'' /* query */ ,
						'Open▹' . 'http://www.packal.org/workflow/spotify-mini-player' /* other_settings*/ ,
						'' /* other_action */ ,
						'' /* artist_name */ ,
						'' /* track_name */ ,
						'' /* album_name */ ,
						'' /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						'' /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), 'Download workflow from Packal', "This will open the Spotify Mini Player Packal page with your default browser", './images/packal.png', 'yes', null, '');
		}

	}
	echo $w->tojson();
	return;
}


/**
 * firstDelimiterPlayQueue function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterPlayQueue($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$all_playlists             = $settings->all_playlists;
	$is_alfred_playlist_active = $settings->is_alfred_playlist_active;
	$radio_number_tracks       = $settings->radio_number_tracks;
	$now_playing_notifications = $settings->now_playing_notifications;
	$max_results               = $settings->max_results;
	$alfred_playlist_uri       = $settings->alfred_playlist_uri;
	$alfred_playlist_name      = $settings->alfred_playlist_name;
	$country_code              = $settings->country_code;
	$last_check_update_time    = $settings->last_check_update_time;
	$oauth_client_id           = $settings->oauth_client_id;
	$oauth_client_secret       = $settings->oauth_client_secret;
	$oauth_redirect_uri        = $settings->oauth_redirect_uri;
	$oauth_access_token        = $settings->oauth_access_token;
	$oauth_expires             = $settings->oauth_expires;
	$oauth_refresh_token       = $settings->oauth_refresh_token;
	$display_name              = $settings->display_name;
	$userid                    = $settings->userid;
	$echonest_api_key          = $settings->echonest_api_key;
	$use_mopidy                = $settings->use_mopidy;

	if ($use_mopidy) {
		$playqueue = $w->read('playqueue.json');
		if ($playqueue == false) {
			$w->result(null, 'help', "There is no track in the play queue", "Make sure to always use the workflow to launch tracks, playlists, etc..Internet connectivity is also required", './images/warning.png', 'no', null, '');
			$w->result(null, serialize(array(
						'' /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'' /* spotify_command */ ,
						'' /* query */ ,
						'Open▹' . 'http://alfred-spotify-mini-player.com/articles/play-queue/' /* other_settings*/ ,
						'' /* other_action */ ,
						'' /* artist_name */ ,
						'' /* track_name */ ,
						'' /* album_name */ ,
						'' /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						'' /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), 'Learn more about Play Queue', "Find out all information about Play Queue on alfred-spotify-mini-player.com", './images/website.png', 'yes', null, '');
			echo $w->tojson();
			exit;
		}
		$tl_tracks = invokeMopidyMethod($w, "core.tracklist.get_tl_tracks", array());
		$current_tl_track = invokeMopidyMethod($w, "core.playback.get_current_tl_track", array());

		$isShuffleEnabled = invokeMopidyMethod($w, "core.tracklist.get_random", array());
		if ($isShuffleEnabled) {
			$w->result(null, 'help', "Shuffle is enabled", "The order of tracks presented below is not relevant", './images/warning.png', 'no', null, '');
		}
		$noresult = true;
		$firstTime = true;
		$nb_tracks           = 0;
		$track_name = '';
		$album_name = '';
		$playlist_name = '';
		$current_track_found = false;
		$current_track_index = 0;
		foreach ($tl_tracks as $tl_track) {
			$current_track_index++;
			if ($current_track_found == false &&
				$tl_track->tlid == $current_tl_track->tlid) {
				$current_track_found = true;
			}
			if ($current_track_found == false &&
				$tl_track->tlid != $current_tl_track->tlid) {
				continue;
			}
			if ($firstTime == true) {
				$added = '🔈 ';
				if ($playqueue->type == 'playlist') {
					$playlist_name = $playqueue->name;
				} elseif ($playqueue->type == 'album') {
					$album_name = $playqueue->name;
				} elseif ($playqueue->type == 'track') {
					$track_name = $playqueue->name;
				}
				$w->result(null, 'help', "Playing from: " . ucfirst($playqueue->type) . ' ' . $playqueue->name, 'Track ' . $current_track_index . ' on '. count($tl_tracks) . ' tracks queued', './images/play_queue.png', 'no', null, '');
				// $subtitle = "⌥ (play album) ⌘ (play artist) ctrl (lookup online)";
				// $subtitle = "$subtitle fn (add track to ...) ⇧ (add album to ...)";
				// $w->result(null, 'help', "Select a track below to play it (or choose alternative described below)", $subtitle, './images/info.png', 'no', null, '');
			}
			$firstTime = false;
			$max_tracks_displayed = 150;
			if ($nb_tracks >= $max_tracks_displayed) {
				$w->result(null, 'help', "[...] " . (count($tl_tracks) - $max_tracks_displayed) . " additional tracks are in the queue", "A maximum of " . $max_tracks_displayed . " tracks is displayed." , './images/info.png', 'no', null, '');
				break;
			}
			$track_name = '';
			if (isset($tl_track->track->name)) {
				$track_name = $tl_track->track->name;
			}
			$artist_name = '';
			if (isset($tl_track->track->artists[0]->name)) {
				$artist_name = $tl_track->track->artists[0]->name;
			}
			$album_name = '';
			if (isset($tl_track->track->album->name)) {
				$album_name = $tl_track->track->album->name;
			}
			$duration = 'na';
			if (isset($tl_track->track->length)) {
				$duration = beautifyTime($tl_track->track->length / 1000);
			}
			$track_artwork = getTrackOrAlbumArtwork($w, $tl_track->track->uri, false);

			if (strpos($track_name,'[unplayable]') !== false) {
			    $track_name = str_replace('[unplayable]', '', $track_name);
			    $w->result(null, '', '🚫 ' . escapeQuery(ucfirst($artist_name)) . " ● " . escapeQuery($track_name), $duration . " ● " . $album_name, $track_artwork, 'no', null, '');
			} else {
				$w->result(null, serialize(array(
							$tl_track->track->uri /*track_uri*/ ,
							'' /* album_uri */ ,
							'' /* artist_uri */ ,
							'' /* playlist_uri */ ,
							'' /* spotify_command */ ,
							'' /* query */ ,
							'' /* other_settings*/ ,
							'play_track_from_play_queue' /* other_action */ ,
							escapeQuery($artist_name) /* artist_name */ ,
							escapeQuery($track_name) /* track_name */ ,
							escapeQuery($album_name) /* album_name */ ,
							$track_artwork /* track_artwork_path */ ,
							'' /* artist_artwork_path */ ,
							'' /* album_artwork_path */ ,
							$playlist_name /* playlist_name */ ,
							'' /* playlist_artwork_path */
						)), $added . escapeQuery($artist_name) . " ● " . escapeQuery($track_name), array(
						$duration . " ● " . escapeQuery($album_name),
						'alt' => 'Play album ' . escapeQuery($album_name) . ' in Spotify',
						'cmd' => 'Play artist ' . escapeQuery($artist_name) . ' in Spotify',
						'fn' => 'Add track ' . escapeQuery($track->name) . ' to ...',
						'shift' => 'Add album ' . escapeQuery($album_name) . ' to ...',
						'ctrl' => 'Search artist ' . escapeQuery($artist_name) . ' online'
					), $track_artwork, 'yes', null, '');
			}
			$noresult      = false;
			$added = '';
			$nb_tracks += 1;
		}

		if ($noresult) {
			$w->result(null, 'help', "There is no track in the play queue from Mopidy", "Make sure to always use the workflow to launch tracks, playlists, etc..Internet connectivity is also required", './images/warning.png', 'no', null, '');
			$w->result(null, serialize(array(
						'' /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'' /* spotify_command */ ,
						'' /* query */ ,
						'Open▹' . 'http://alfred-spotify-mini-player.com/articles/play-queue/' /* other_settings*/ ,
						'' /* other_action */ ,
						'' /* artist_name */ ,
						'' /* track_name */ ,
						'' /* album_name */ ,
						'' /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						'' /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), 'Learn more about Play Queue', "Find out all information about Play Queue on alfred-spotify-mini-player.com", './images/website.png', 'yes', null, '');
			echo $w->tojson();
			exit;
		}
	} else {
		$playqueue = $w->read('playqueue.json');
		if ($playqueue == false) {
			$w->result(null, 'help', "There is no track in the play queue", "Make sure to always use the workflow to launch tracks, playlists, etc..Internet connectivity is also required", './images/warning.png', 'no', null, '');
			$w->result(null, serialize(array(
						'' /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'' /* spotify_command */ ,
						'' /* query */ ,
						'Open▹' . 'http://alfred-spotify-mini-player.com/articles/play-queue/' /* other_settings*/ ,
						'' /* other_action */ ,
						'' /* artist_name */ ,
						'' /* track_name */ ,
						'' /* album_name */ ,
						'' /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						'' /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), 'Learn more about Play Queue', "Find out all information about Play Queue on alfred-spotify-mini-player.com", './images/website.png', 'yes', null, '');
			echo $w->tojson();
			exit;
		}
		$command_output = exec("osascript -e '
        tell application \"Spotify\"
        if shuffling enabled is true then
            if shuffling is true then
                return \"enabled\"
            else
                return \"disabled\"
            end if
        else
            return \"disabled\"
        end if
        end tell'");
		if ($command_output == "enabled") {
			$w->result(null, 'help', "Shuffle is enabled", "The order of tracks presented below is not relevant", './images/warning.png', 'no', null, '');
		}
		$noresult = true;
		$nb_tracks           = 0;
		$track_name = '';
		$album_name = '';
		$playlist_name = '';
		for ($i = $playqueue->current_track_index; $i < count($playqueue->tracks);$i++) {
			$track = $playqueue->tracks[$i];
			if ($noresult == true) {
				$added = '🔈 ';
				if ($playqueue->type == 'playlist') {
					$playlist_name = $playqueue->name;
				} elseif ($playqueue->type == 'album') {
					$album_name = $playqueue->name;
				} elseif ($playqueue->type == 'track') {
					$track_name = $playqueue->name;
				}
				$w->result(null, 'help', "Playing from: " . ucfirst($playqueue->type) . ' ' . $playqueue->name, 'Track ' . ($playqueue->current_track_index + 1) . ' on '. count($playqueue->tracks) . ' tracks queued', './images/play_queue.png', 'no', null, '');
				// $subtitle = "⌥ (play album) ⌘ (play artist) ctrl (lookup online)";
				// $subtitle = "$subtitle fn (add track to ...) ⇧ (add album to ...)";
				// $w->result(null, 'help', "Select a track below to play it (or choose alternative described below)", $subtitle, './images/info.png', 'no', null, '');
			}
			$max_tracks_displayed = 150;
			if ($nb_tracks >= $max_tracks_displayed) {
				$w->result(null, 'help', "[...] " . (count($playqueue->tracks) - $max_tracks_displayed) . " additional tracks are in the queue", "A maximum of " . $max_tracks_displayed . " tracks is displayed." , './images/info.png', 'no', null, '');
				break;
			}
			$track_name = '';
			if (isset($track->name)) {
				$track_name = $track->name;
			}
			$artist_name = '';
			if (isset($track->artists[0]->name)) {
				$artist_name = $track->artists[0]->name;
			}
			$album_name = '';
			if (isset($track->album->name)) {
				$album_name = $track->album->name;
			}
			$duration = 'na';
			if (isset($track->duration_ms)) {
				$duration = beautifyTime($track->duration_ms / 1000);
			}
			if (isset($track->duration)) {
				$duration = $track->duration;
			}
			$track_artwork = getTrackOrAlbumArtwork($w, $track->uri, false);
			$w->result(null, serialize(array(
						$track->uri /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'' /* spotify_command */ ,
						'' /* query */ ,
						'' /* other_settings*/ ,
						'play_track_from_play_queue' /* other_action */ ,
						escapeQuery($artist_name) /* artist_name */ ,
						escapeQuery($track_name) /* track_name */ ,
						escapeQuery($album_name) /* album_name */ ,
						$track_artwork /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						$playlist_name /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), $added . escapeQuery($artist_name) . " ● " . escapeQuery($track_name), array(
					$duration . " ● " . escapeQuery($album_name),
					'alt' => 'Play album ' . escapeQuery($album_name) . ' in Spotify',
					'cmd' => 'Play artist ' . escapeQuery($artist_name) . ' in Spotify',
					'fn' => 'Add track ' . escapeQuery($track->name) . ' to ...',
					'shift' => 'Add album ' . escapeQuery($album_name) . ' to ...',
					'ctrl' => 'Search artist ' . escapeQuery($artist_name) . ' online'
				), $track_artwork, 'yes', null, '');
			$noresult      = false;
			$added = '';
			$nb_tracks += 1;
		}

		if ($noresult) {
			$w->result(null, 'help', "There is no track in the play queue", "Make sure to always use the workflow to launch tracks, playlists, etc..Internet connectivity is also required", './images/warning.png', 'no', null, '');
			$w->result(null, serialize(array(
						'' /*track_uri*/ ,
						'' /* album_uri */ ,
						'' /* artist_uri */ ,
						'' /* playlist_uri */ ,
						'' /* spotify_command */ ,
						'' /* query */ ,
						'Open▹' . 'http://alfred-spotify-mini-player.com/articles/play-queue/' /* other_settings*/ ,
						'' /* other_action */ ,
						'' /* artist_name */ ,
						'' /* track_name */ ,
						'' /* album_name */ ,
						'' /* track_artwork_path */ ,
						'' /* artist_artwork_path */ ,
						'' /* album_artwork_path */ ,
						'' /* playlist_name */ ,
						'' /* playlist_artwork_path */
					)), 'Learn more about Play Queue', "Find out all information about Play Queue on alfred-spotify-mini-player.com", './images/website.png', 'yes', null, '');
			echo $w->tojson();
			exit;
		}
	}
}


/**
 * firstDelimiterBrowse function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterBrowse($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$country_code = $settings->country_code;

	$w->result(null, '', getCountryName($country_code), 'Browse the Spotify categories in ' . getCountryName($country_code), './images/browse.png', 'no', null, 'Browse▹' . $country_code . '▹');

	if ($country_code != 'US') {
		$w->result(null, '', getCountryName('US'), 'Browse the Spotify categories in ' . getCountryName('US'), './images/browse.png', 'no', null, 'Browse▹US▹');
	}

	if ($country_code != 'GB') {
		$w->result(null, '', getCountryName('GB'), 'Browse the Spotify categories in ' . getCountryName('GB'), './images/browse.png', 'no', null, 'Browse▹GB▹');
	}

	$w->result(null, '', 'Choose Another country', 'Browse the Spotify categories in another country of your choice', './images/browse.png', 'no', null, 'Browse▹Choose a Country▹');
}

/**
 * firstDelimiterYourTops function.
 *
 * @access public
 * @param mixed $w
 * @param mixed $query
 * @param mixed $settings
 * @param mixed $db
 * @param mixed $update_in_progress
 * @return void
 */
function firstDelimiterYourTops($w, $query, $settings, $db, $update_in_progress) {
	$words = explode('▹', $query);
	$kind  = $words[0];

	$w->result(null, '', 'Get your top artists (last 4 weeks)', 'Get your top artists for last 4 weeks', './images/your_tops_artists.png', 'no', null, 'Your Tops▹Artists▹short_term');

	$w->result(null, '', 'Get your top artists (last 6 months)', 'Get your top artists for last 6 months', './images/your_tops_artists.png', 'no', null, 'Your Tops▹Artists▹medium_term');

	$w->result(null, '', 'Get your top artists (all time)', 'Get your top artists for all time', './images/your_tops_artists.png', 'no', null, 'Your Tops▹Artists▹long_term');

	$w->result(null, '', 'Get your top tracks (last 4 weeks)', 'Get your top tracks for last 4 weeks', './images/your_tops_tracks.png', 'no', null, 'Your Tops▹Tracks▹short_term');

	$w->result(null, '', 'Get your top tracks (last 6 months)', 'Get your top tracks for last 6 months', './images/your_tops_tracks.png', 'no', null, 'Your Tops▹Tracks▹medium_term');

	$w->result(null, '', 'Get your top tracks (all time)', 'Get your top tracks for all time', './images/your_tops_tracks.png', 'no', null, 'Your Tops▹Tracks▹long_term');
}
