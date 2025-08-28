<template>
    <div class="container">
        <h1>Мої плейлисти</h1>

    <div class="new-playlist">
        <input v-model="newPlaylistName" placeholder="Нова назва плейлиста" />
        <button @click="createNewPlaylist">Створити</button>
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
                    <button @click="removeTrack(pl.id, track.id)">❌</button>
                </li>
            </ul>

            <button @click="addTrack(pl.id)">Додати трек</button>
        </div>
    </div>

        <div v-else>
            <h4>Плейлистів ще немає.</h4>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';

const store = useStore();

const newPlaylistName = ref('');

const playlists = store.getters.playlists;
const loading = store.getters.loading;
const error = store.getters.error;

const tracksOf = (playlistId) => store.getters.tracksOf(playlistId);

async function fetchPlaylists() {
    await store.dispatch('fetchPlaylists');
}

async function createNewPlaylist() {
    if (!newPlaylistName.value.trim()) return alert('Введіть назву плейлиста');
    try {
        await store.dispatch('createPlaylist', newPlaylistName.value.trim());
        newPlaylistName.value = '';
    } catch (err) {
        alert(err.message);
    }
}

async function addTrack(playlistId) {
    const song = {
        id: Date.now(),
        name: 'Test Song',
        artist_name: 'Artist',
        album_image: null,
        audio: 'https://example.com/audio.mp3',
        duration_sec: 180
    };
    try {
        await store.dispatch('addTrackToPlaylist', { playlistId, song });
    } catch (err) {
        alert(err.message);
    }
}

async function removeTrack(playlistId, trackId) {
    try {
        await store.dispatch('removeTrackFromPlaylist', { playlistId, trackId });
    } catch (err) {
        alert(err.message);
    }
}

onMounted(() => {
    fetchPlaylists();
});
</script>

<style scoped lang="scss">
.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 1rem;
}
.new-playlist {
    display: flex;
    gap: 8px;
    margin-bottom: var(--m-space-16);
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
</style>
