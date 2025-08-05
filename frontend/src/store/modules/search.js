import axios from "axios";

const clientId = process.env.JAMENDO_CLIENT_ID;

export default {
    state() {
        return {
            searchValue: '',
            songs: [],
            currentSong: null,
            contSearch: Number(localStorage.getItem('contSearch')) || 0,
        }
    },
    actions: {
        async fetchSongs({ state, commit }) { 
        const endpoint = `https://api.jamendo.com/v3.0/tracks/?client_id=${clientId}&format=json&limit=10&search=${encodeURIComponent(state.searchValue)}`;

        try {
            const response = await axios.get(endpoint);
            commit('setSongs', response.data.results); 
            } catch (error) {
                console.error('❌ Помилка при запиті пісень з Jamendo:', error);
            }
        },

    },

    mutations: {
        getInputValue(state, value) {
            state.searchValue = value
        },
        setSongs(state, songs) {
            state.songs = songs
        },
        setCurrentSong(state, song) {  
            state.currentSong = song
        },
        calculateSearch(state) {
            state.contSearch++;
            localStorage.setItem('contSearch', state.contSearch);
        },
        resetSearchCount(state) {
            state.contSearch = 0;
            localStorage.removeItem('contSearch');
        },
    },

    getters: {
        songMatch(state) {
            return state.songs;
        },
        currentSong(state) {
            return state.currentSong;
        },
        countSearch(state) {
            return state.contSearch
        }
    },
}