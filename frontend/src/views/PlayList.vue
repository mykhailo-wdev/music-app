<template>
    <div class="container">
        <transition name="slide-fade">
            <music-alert
                v-if="showAlert"
                :alertTitle="alertConfig.title"
                :alertDescription="alertConfig.description"
                :buttons="alertConfig.buttons"
            ></music-alert>
        </transition>
        <h2>Мої плейлисти</h2>
    <div class="new-playlist">
        <input v-model="newPlaylistName" @keyup.enter="createNewPlaylist" placeholder="Нова назва плейлиста" />
        <music-button @click="createNewPlaylist" type-btn="btn-sky" text="Створити"></music-button>
        <p v-if="errorEmptyPlaylist" class="error">Поле не може бути пустим. Введіть назву плейлиста</p>
    </div>
    <div v-if="loading"></div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else-if="allPlaylists.length > 0" class="playlists">
        <div v-for="pl in allPlaylists" :key="pl.id" class="playlist-card">
            <div class="playlist-head">
                <h4>{{ pl.name }}</h4>
                <small>Оновлено: {{ pl.updated_at_local || pl.updated_at }}</small>
                <br>
                <div class="playlist-btns">
                    <music-button 
                        type-btn="btn-fresh" 
                        text="Слухати" 
                        @action="() => playPlaylist(pl.id)"
                    ></music-button>
                    <music-button 
                        type-btn="btn-hot" 
                        text="Видалити" 
                        @action="askDelete(pl.id)">
                    </music-button>
                    <button @click="toggleMusicAll(pl.id)" class="more-info">
                        <svg width="7" height="26" viewBox="0 0 7 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.5 19.5C4.19223 19.5 4.86892 19.6906 5.44449 20.0477C6.02007 20.4048 6.46867 20.9124 6.73358 21.5063C6.99848 22.1001 7.0678 22.7536 6.93275 23.384C6.7977 24.0145 6.46436 24.5936 5.97487 25.0481C5.48539 25.5026 4.86175 25.8121 4.18282 25.9375C3.50388 26.063 2.80015 25.9986 2.16061 25.7526C1.52107 25.5066 0.974441 25.0901 0.589857 24.5556C0.205272 24.0211 0 23.3928 0 22.75C0 21.888 0.36875 21.0614 1.02513 20.4519C1.6815 19.8424 2.57174 19.5 3.5 19.5ZM0 3.25C0 3.89279 0.205272 4.52114 0.589857 5.0556C0.974441 5.59006 1.52107 6.00662 2.16061 6.25261C2.80015 6.49859 3.50388 6.56295 4.18282 6.43755C4.86175 6.31215 5.48539 6.00262 5.97487 5.5481C6.46436 5.09358 6.7977 4.51448 6.93275 3.88404C7.0678 3.2536 6.99848 2.60014 6.73358 2.00628C6.46867 1.41242 6.02007 0.904838 5.44449 0.547724C4.86892 0.190609 4.19223 0 3.5 0C2.57174 0 1.6815 0.34241 1.02513 0.951903C0.36875 1.5614 0 2.38805 0 3.25ZM0 13C0 13.6428 0.205272 14.2711 0.589857 14.8056C0.974441 15.3401 1.52107 15.7566 2.16061 16.0026C2.80015 16.2486 3.50388 16.313 4.18282 16.1876C4.86175 16.0621 5.48539 15.7526 5.97487 15.2981C6.46436 14.8436 6.7977 14.2645 6.93275 13.634C7.0678 13.0036 6.99848 12.3501 6.73358 11.7563C6.46867 11.1624 6.02007 10.6548 5.44449 10.2977C4.86892 9.94061 4.19223 9.75 3.5 9.75C2.57174 9.75 1.6815 10.0924 1.02513 10.7019C0.36875 11.3114 0 12.138 0 13Z" fill="black"/>
                        </svg>
                    </button>
                </div>
            </div>
            <ul class="track-list" v-if="openPlaylistId === pl.id">
                <li class="track-item" v-for="(track, idx) in tracksOf(pl.id)" :key="track.id">
                    <span>{{ idx + 1 }}.</span>
                    {{ toUpperCaseFirstLetter(track.track_name) }} – {{ toUpperCaseFirstLetter(track.artist_name) }}
                    |
                    <button class="delete-track" @click="removeTrack(pl.id, track.id)">
                        <img :src="require('@/assets/images/delete-icon.svg')" loading="lazy" alt="Delete icon" width="26" height="26" fetchpriority="low">
                    </button> 
                </li>
            </ul>
        </div>
    </div>
        <div v-else>
            <div class="loader-wrap">
                <div class="loader"></div>
            </div>
        </div>
        <music-player></music-player>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useStore } from 'vuex';
import MusicButton from '@/components/MusicButton.vue';
import MusicAlert from '@/components/MusicAlert.vue';
import MusicPlayer from '@/components/MusicPlayer.vue';

const store = useStore();

const newPlaylistName = ref('');
const errorEmptyPlaylist = ref(false);
const showAlert = ref(false);
const playlistToDelete = ref(null);
const openPlaylistId = ref(null);  
const pageLoading = ref(true); 


const alertConfig = ref({
    title: '',
    description: '',
    buttons: []
});

const playlists = ref([]);
const loadingStore = computed(() => store.getters.loading);
const errorStore = computed(() => store.getters.error);
const isLoading = computed(() => loadingStore.value || pageLoading.value);
const loading = computed(() => store.getters.loading);
const error = computed(() => store.getters.error);

const allTracksMap = ref({});

function openAlert(config) {
    alertConfig.value = config;
    showAlert.value = true;
}

function closeAlert() {
    showAlert.value = false;
    playlistToDelete.value = null;
}

function toggleMusicAll(id) {
    openPlaylistId.value = openPlaylistId.value === id ? null : id;
}

function tracksOf(playlistId) {
    if (playlistId === 'favorites') {
        return store.getters['favoritesTracks'] || [];
    }
    return store.getters.tracksOf(playlistId) || [];
}


async function fetchPlaylists() {
    try {
        await store.dispatch('fetchPlaylists');
        playlists.value = store.getters.playlists || [];
        await Promise.all(
            playlists.value.map(pl => store.dispatch('fetchPlaylistTracks', pl.id))
        );
        await store.dispatch('loadFavoritesPlaylist');
        const map = {};
        playlists.value.forEach(pl => {
            pl.tracks?.forEach(track => {
                map[track.id] = track;
            });
        });
        store.getters['favoritesTracks'].forEach(track => {
            map[track.id] = track;
        });
        allTracksMap.value = map;
        allPlaylists.value = [
            {
                id: 'favorites',
                name: 'Favorites',
                tracks: store.getters['favoritesTracks'] || []
            },
            ...playlists.value
        ];

    } catch (err) {
        openAlert({
            title: 'Помилка',
            description: 'Не вдалося завантажити плейлисти',
            buttons: [{ text: 'OK', typeBtn: 'btn-hot', action: closeAlert }]
        });
    }
}

async function createNewPlaylist() {
    if (!newPlaylistName.value.trim()) {
        errorEmptyPlaylist.value = true;
        return;
    }
    errorEmptyPlaylist.value = false;
    try {
        const name = newPlaylistName.value.trim();
        await store.dispatch('createPlaylist', name);
        newPlaylistName.value = '';
        openAlert({
            title: 'Успіх',
            description: `Плейлист "${name}" створено!`,
            buttons: [{ text: 'OK', typeBtn: 'btn-fresh', action: closeAlert }]
        });
        await fetchPlaylists(); 
    } catch (err) {
        openAlert({
            title: 'Помилка',
            description: 'Не вдалося створити плейлист',
            buttons: [{ text: 'Закрити', typeBtn: 'btn-hot', action: closeAlert }]
        });
    }
}

async function removeTrack(playlistId, trackId) {
    try {
        await store.dispatch('removeTrackFromPlaylist', { playlistId, trackId });
        await fetchPlaylists();
    } catch {
        openAlert({
            title: 'Помилка',
            description: 'Не вдалося видалити трек',
            buttons: [{ text: 'OK', typeBtn: 'btn-hot', action: closeAlert }]
        });
    }
}

async function confirmDelete() {
    if (!playlistToDelete.value) return;
    try {
        await store.dispatch('deletePlaylist', playlistToDelete.value);
        await fetchPlaylists();
    } catch {
        openAlert({
            title: 'Помилка',
            description: 'Не вдалося видалити плейлист',
            buttons: [{ text: 'OK', typeBtn: 'btn-hot', action: closeAlert }]
        });
    } finally {
        closeAlert();
    }
}

function askDelete(id) {
    playlistToDelete.value = id;
    openAlert({
        title: 'Ви дійсно хочете видалити цей плейлист?',
        description: 'Цією дією Ви підтверджуєте його видалення назавжди',
        buttons: [
            { text: 'Закрити', typeBtn: 'btn-hot', action: closeAlert },
            { text: 'Видалити', typeBtn: 'btn-fresh', action: confirmDelete }
        ]
    });
}

function playPlaylist(playlistId, tracks = null) {
    const list = (playlistId === 'favorites')
    ? tracksOf(playlistId) 
    : (tracks || tracksOf(playlistId));
    if (!list.length) {
        openAlert({
            title: 'Порожній плейлист',
            description: 'У цьому плейлисті ще немає треків',
            buttons: [{ text: 'OK', typeBtn: 'btn-hot', action: closeAlert }]
        });
        return;
    }
    store.dispatch('startPlaylist', { playlistId, tracks: list });
}

function toUpperCaseFirstLetter(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

const allPlaylists = ref([]);

onMounted(async () => {
    await fetchPlaylists();
});

</script>




<style scoped lang="scss">
@use '../assets/styles/mixins';
.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 16px;
}
.new-playlist {
    display: grid;
    grid-template-columns: 1.6fr auto;
    gap: 8px;
    margin-bottom: var(--m-space-16);
    input {
        outline: none;
        border: 1px solid var(--palette-stroke);
        text-indent: 16px;
        @include mixins.text-small();
        border-radius: 8px;
    }
}
.playlists {
    display: flex;
    flex-direction: column;
    gap: var(--m-space-16);
}
.playlist-card {
    border: 1px solid var(--palette-stroke);
    border-radius: 8px;
    max-height: 280px;
    overflow-y: scroll;
}
.playlist-head {
    position: sticky;
    top: 0;
    background-color: var(--palette-white);
    padding: var(--m-space-16);
}
.error {
    color: var(--palette-error);
}
small {
    @include mixins.text-small();
    display: inline-block;
}
.playlist-btns {
    margin-top: 8px;
    display: inline-grid;
    align-items: center;
    grid-template-columns: auto auto auto;
    gap: 8px;
}
.more-info {
    cursor: pointer;
    margin-left: 8px;
    display: inline-block;
    width: 10px;
    height: 26px;
    svg {
        height: 28px;
    }
}
.delete-track {
    cursor: pointer;
    margin-left: 4px;
}
.track-list {
    padding: 0 8px;  
}
.track-item {
    display: flex;
    align-items: center;
    padding: 8px;
    border-top: 1px solid var(--palette-stroke);
    @include mixins.text-small();
}
.loader-wrap {
    display: flex;
    justify-content: center;
}
.loader {
    width: 100px;
    aspect-ratio: 1;
    display: grid;
    border: 4px solid #0000;
    border-radius: 50%;
    border-color: #ccc #0000;
    animation: l16 1s infinite linear;
}
.loader::before,
.loader::after {    
    content: "";
    grid-area: 1/1;
    margin: 2px;
    border: inherit;
    border-radius: 50%;
}
.loader::before {
    border-color: #f03355 #0000;
    animation: inherit; 
    animation-duration: .5s;
    animation-direction: reverse;
}
.loader::after {
    margin: 8px;
}
@keyframes l16 { 
    100%{transform: rotate(1turn)}
}

</style>
