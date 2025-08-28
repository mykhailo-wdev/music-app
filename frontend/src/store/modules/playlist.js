// store/playlist.js
import axios from 'axios';

const API_BASE_URL = process.env.VUE_APP_API_BASE_URL || 'http://localhost:8000/api';

export default {
    state: () => ({
        playlists: [],
        tracksByPlaylist: {},
        loading: false,
        error: null,
    }),

    mutations: {
        setLoading(state, value) { 
            state.loading = value; 
        },
        setError(state, value) { 
            state.error = value; 
        },
        setPlaylists(state, list) { 
            state.playlists = list; 
        },
        setPlaylistTracks(state, { playlistId, tracks }) {
            state.tracksByPlaylist = { ...state.tracksByPlaylist, [playlistId]: tracks };
        },
        addPlaylist(state, playlist) { 
            state.playlists = [playlist, ...state.playlists]; 
        },
    },

    actions: {
        async fetchPlaylists({ commit }) {
        commit('setLoading', true);
        commit('setError', null);
        try {
            const { data } = await axios.get(`${API_BASE_URL}/playlists.php`);
            if (data.status === 'success') commit('setPlaylists', data.data || []);
            else commit('setError', data.message || 'Error fetching playlists');
        } catch (err) {
            commit('setError', err.response?.data?.message || err.message || 'Server error');
        } finally {
            commit('setLoading', false);
        }
        },

        async createPlaylist({ commit }, name) {
        try {
            const { data } = await axios.post(`${API_BASE_URL}/playlists.php`, { name });
            if (data.status === 'success') commit('addPlaylist', data.data);
            else throw new Error(data.message || 'Failed to create playlist');
        } catch (err) {
            throw new Error(err.response?.data?.message || err.message || 'Server error');
        }
        },

        async fetchPlaylistTracks({ commit }, playlistId) {
        try {
            const { data } = await axios.get(`${API_BASE_URL}/playlist_tracks.php`, {
            params: { playlist_id: playlistId },
            });
            if (data.status === 'success') commit('setPlaylistTracks', { playlistId, tracks: data.data || [] });
            else throw new Error(data.message || 'Failed to load tracks');
        } catch (err) {
            throw new Error(err.response?.data?.message || err.message || 'Server error');
        }
        },

        async addTrackToPlaylist({ dispatch }, { playlistId, song }) {
        const payload = {
            playlist_id: playlistId,
            track_source_id: song.id,
            track_name: song.name,
            artist_name: song.artist_name,
            album_image: song.album_image || null,
            audio_url: song.audio,
            duration_sec: song.duration_sec || null,
        };
        try {
            const { data } = await axios.post(`${API_BASE_URL}/playlist_add_track.php`, payload);
            if (data.status !== 'success') throw new Error(data.message || 'Add failed');
            await dispatch('fetchPlaylistTracks', playlistId);
        } catch (err) {
            throw new Error(err.response?.data?.message || err.message || 'Server error');
        }
        },

        async removeTrackFromPlaylist({ dispatch }, { playlistId, trackId }) {
        try {
            const { data } = await axios.post(`${API_BASE_URL}/playlist_remove_track.php`, {
                playlist_id: playlistId,
                track_id: trackId,
            });
            if (data.status !== 'success') throw new Error(data.message || 'Remove failed');
            await dispatch('fetchPlaylistTracks', playlistId);
            } catch (err) {
                throw new Error(err.response?.data?.message || err.message || 'Server error');
            }
        },
    },

    getters: {
        playlists: (state) => state.playlists,
        tracksOf: (state) => (playlistId) => state.tracksByPlaylist[playlistId] || [],
        loading: (state) => state.loading,
        error: (state) => state.error,
    },
};
