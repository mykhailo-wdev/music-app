// store/player.js
export default {
    state: () => ({
        currentPlaylist: [],
        currentTrackIndex: 0,
        playing: false,
        visible: false,
        repeat: false,
        random: false 
    }),
    mutations: {
        setPlaylist(state, tracks) {
            state.currentPlaylist = tracks;
            state.currentTrackIndex = 0;
        },
        setPlaying(state, playing) {
            state.playing = playing;
        },
        nextTrack(state) {
            if (state.toggleRepeat) return;
            state.currentTrackIndex = 
                (state.currentTrackIndex + 1) % state.currentPlaylist.length;
        },
        prevTrack(state) {
            if (state.toggleRepeat) return;
            state.currentTrackIndex =
            (state.currentTrackIndex - 1 + state.currentPlaylist.length) % state.currentPlaylist.length;
        },
        setVisible(state, visible) {
            state.visible = visible;
        },
        setTrackIndex(state, index) {
            state.currentTrackIndex = index;
        },
        toggleRepeat(state) {
            state.repeat = !state.repeat;
        },
        toggleRandom(state) {
            state.random = !state.random;
        },
    },
    actions: {
        startPlaylist({ commit }, { tracks }) {
            commit('setPlaylist', tracks);
            commit('setTrackIndex', 0);
            commit('setVisible', true);
            commit('setPlaying', true);
        },
        togglePlay({ commit, state }) {
            commit('setPlaying', !state.playing);
        }
    }
};

