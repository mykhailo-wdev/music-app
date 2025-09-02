<template>
    <div class="container">
        <h1>Мої плейлисти</h1>

    <div class="new-playlist">
        <input v-model="newPlaylistName" placeholder="Нова назва плейлиста" />
        <music-button @click="createNewPlaylist" type-btn="btn-sky" text="Створити"></music-button>
        <p v-if="errorEmptyPlaylist">Поле не може бути пусте. Введіть назву плейлиста</p>
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
            <div class="playlist-btns">
                <music-button @click="addTrack(pl.id)"  type-btn="btn-sunny" text="Додати трек"></music-button>
                <music-button type-btn="btn-fresh" text="Слухати плейлист"></music-button>
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

const store = useStore();

const newPlaylistName = ref('');
const errorEmptyPlaylist = ref(false);

const playlists = computed(() => store.getters.playlists);
const loading   = computed(() => store.getters.loading);
const error     = computed(() => store.getters.error);

const tracksOf = (playlistId) => store.getters.tracksOf(playlistId);

async function fetchPlaylists() {
    await store.dispatch('fetchPlaylists');
}

async function createNewPlaylist() {
    if (!newPlaylistName.value.trim()) return errorEmptyPlaylist.value = true;
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

watch(playlists, (list) => {
    list?.forEach(pl => store.dispatch('fetchPlaylistTracks', pl.id));
}, { immediate: true });

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
    display: inline-grid;
    grid-template-columns: auto auto;
    gap: 8px;
}
</style>
