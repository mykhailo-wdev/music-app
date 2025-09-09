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

        <h1>Мої плейлисти</h1>

    <div class="new-playlist">
        <input v-model="newPlaylistName" @keyup.enter="createNewPlaylist" placeholder="Нова назва плейлиста" />
        <music-button @click="createNewPlaylist" type-btn="btn-sky" text="Створити"></music-button>
        <p v-if="errorEmptyPlaylist" class="error">Поле не може бути пустим. Введіть назву плейлиста</p>
    </div>

    <div v-if="loading">Завантаження плейлистів...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <div v-if="playlists.length > 0" class="playlists">
        <div v-for="pl in playlists" :key="pl.id" class="playlist-card">
            <h3>{{ pl.name }}</h3>
            <small>Оновлено: {{ pl.updated_at }}</small>

            <ul>
                <li v-for="track in tracksOf(pl.id)" :key="track.id">
                    {{ track.track_name }} – {{ track.artist_name }}
                    <button class="delete-track" @click="removeTrack(pl.id, track.id)">❌</button>
                </li>
            </ul>
            <div class="playlist-btns">
                <music-button 
                       type-btn="btn-fresh" 
                       text="Слухати плейлист" 
                       @action="() => playPlaylist(pl.id)"
                  ></music-button>
                <music-button type-btn="btn-hot" text="Видалити" @action="askDelete(pl.id)"></music-button>
            </div>
        </div>
    </div>

        <div v-else>
            <h4>Плейлистів ще немає.</h4>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useStore } from 'vuex';
import MusicButton from '@/components/MusicButton.vue';
import MusicAlert from '@/components/MusicAlert.vue';

const store = useStore();

const newPlaylistName = ref('');
const errorEmptyPlaylist = ref(false);
const showAlert = ref(false);
const playlistToDelete = ref(null);

const alertConfig = ref({
    title: '',
    description: '',
    buttons: []
});

const playlists = computed(() => store.getters.playlists);
const loading   = computed(() => store.getters.loading);
const error     = computed(() => store.getters.error);

const tracksOf = (playlistId) => store.getters.tracksOf(playlistId);

function openAlert(config) {
    alertConfig.value = config;
    showAlert.value = true;
}

async function fetchPlaylists() {
    try {
        await store.dispatch('fetchPlaylists');
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
    } catch (err) {
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
    } catch (err) {
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

function closeAlert() {
    showAlert.value = false;
    playlistToDelete.value = null;
}

// слухати плейлист (опціонально, якщо є плеєр)
function playPlaylist(playlistId) {
    const tracks = tracksOf(playlistId);
    if (!tracks.length) {
        openAlert({
            title: 'Порожній плейлист',
            description: 'У цьому плейлисті ще немає треків',
            buttons: [{ text: 'OK', typeBtn: 'btn-hot', action: closeAlert }]
        });
        return;
    }
    store.dispatch('player/startPlaylist', { playlistId, tracks });
}


watch(playlists, (list) => {
    if (list && list.length) {
        list.forEach((pl) => store.dispatch('fetchPlaylistTracks', pl.id));
    }
},
    { 
        immediate: true, 
        deep: false 
    }
);

onMounted(fetchPlaylists);
</script>



<style scoped lang="scss">
@use '../assets/styles/mixins';
.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 1rem;
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
    padding: var(--m-space-16);
}
.error {
    color: var(--palette-error);
}
small {
    @include mixins.text-small();
    display: inline-block;
    margin-bottom: var(--m-space-16);
}
.playlist-btns {
    margin-top: var(--m-space-16);
    display: inline-grid;
    grid-template-columns: auto auto;
    gap: 8px;
}
.delete-track {
    cursor: pointer;
    font-size: 16px;
}
</style>
