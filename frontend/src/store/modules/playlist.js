// store/playlist.js
import backendApi from '@/api/backend';

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
        removePlaylist(state, id) {
            state.playlists = state.playlists.filter(p => p.id !== id);
            delete state.tracksByPlaylist[id];
        }
    },

    actions: {
        async fetchPlaylists({ commit }) {
            commit('setLoading', true);
            commit('setError', null);
            const start = Date.now();
            try {
                const { data } = await backendApi.get('/playlists.php');
                if (data.status === 'success') commit('setPlaylists', data.data || []);
                else commit('setError', data.message || 'Error fetching playlists');
            } catch (err) {
                commit('setError', err.response?.data?.message || err.message || 'Server error');
            } finally {
                const elapsed = Date.now() - start;
                const delay = Math.max(0, 1000 - elapsed);
                await new Promise(r => setTimeout(r, delay)); 
                commit('setLoading', false);
            }
        },
        

        async createPlaylist({ commit }, name) {
            try {
                const { data } = await backendApi.post('/playlists.php', { name });
                if (data.status === 'success') commit('addPlaylist', data.data);
                else throw new Error(data.message || 'Failed to create playlist');
            } catch (err) {
                throw new Error(err.response?.data?.message || err.message || 'Server error');
            }
        },

        async fetchPlaylistTracks({ commit }, playlistId) {
            try {
                const { data } = await backendApi.get('/playlist_tracks.php', {
                    params: { playlist_id: playlistId },
                });
                if (data.status === 'success') {
                    commit('setPlaylistTracks', { playlistId, tracks: data.data || [] });
                } else {
                    throw new Error(data.message || 'Failed to load tracks');
                }
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
                const { data } = await backendApi.post('/playlist_tracks.php', payload);
                if (data.status !== 'success') throw new Error(data.message || 'Add failed');
                await dispatch('fetchPlaylistTracks', playlistId);
            } catch (err) {
                throw new Error(err.response?.data?.message || err.message || 'Server error');
            }
        },

        async removeTrackFromPlaylist({ dispatch }, { playlistId, trackId }) {
            try {
                const { data } = await backendApi.delete('/playlist_tracks.php', {
                    data: { playlist_id: playlistId, track_id: trackId }, 
                });
                if (data.status !== 'success') throw new Error(data.message || 'Remove failed');
                await dispatch('fetchPlaylistTracks', playlistId);
            } catch (err) {
                throw new Error(err.response?.data?.message || err.message || 'Server error');
            }
        },

        async deletePlaylist({ commit }, id) {
            try {
                const { data } = await backendApi.delete('/playlists.php', {
                    params: { id }
                });
                if (data.status === 'success') {
                    commit('removePlaylist', id);
                } else {
                    throw new Error(data.message || 'Delete failed');
                }
            } catch (err) {
                throw new Error(err.response?.data?.message || err.message || 'Server error');
            }
        }

    },

    getters: {
        playlists: (state) => state.playlists,
        tracksOf: (state) => (playlistId) => state.tracksByPlaylist[playlistId] || [],
        loading: (state) => state.loading,
        error: (state) => state.error,
    },
};
