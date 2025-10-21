<template>
    <main class="main-app">
        <div class="container">
            <section class="profile">
                <div class="profile-wrap">
                    <div class="profile-layout">
                        <h3>{{ user?.name }}</h3>
                        <a :href="`mailto:${user?.email}`" class="text-main user-email">{{ user?.email }}</a>
                        <ul class="user-data">
                            <li class="user-qty-folders">
                                <h4>{{ playlistCount }}</h4>
                                <p class="text-main">Папок</p>
                            </li>
                            <li class="user-qty-tracks">
                                <h4>{{ tracksCount }}</h4>
                                <p class="text-main">Треків</p>
                            </li>
                            <li class="user-qty-favorites">
                                <h4>{{ favoritesCount }}</h4>
                                <p class="text-main">Улюблених</p>
                            </li>
                        </ul>
                        <div class="playlist-btn">
                            <music-button type-btn="btn-sky" text="До плейлистів" to="/playlists"></music-button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>

<script setup>
import MusicHeader from '@/components/MusicHeader.vue';
import MusicButton from '@/components/MusicButton.vue';
import { onMounted } from 'vue';
import { useUser } from '@/composables/useUser';
import { useUserPlaylists } from "@/composables/useUserPlaylists";
import { useUserTracks } from '@/composables/useUserTracks';
import { useUserFavorites } from '@/composables/useUserFavorites';


const { user, loading, error } = useUser();
const { playlistCount, fetchPlaylistCount } = useUserPlaylists();
const { tracksCount, fetchTrackCount } = useUserTracks();
const { favoritesCount, fetchFavoritesCount } = useUserFavorites();

onMounted(() => {
    fetchPlaylistCount();
    fetchTrackCount();
    fetchFavoritesCount();
});
</script>

<style lang="scss" scoped>
.main-app {
    background-image: url('../assets/images/profile/profile_bg.webp');
    background-repeat: no-repeat;
    background-size: cover;
    padding: 0;
}

.profile {
    display: flex;
    align-items: center;
    height: calc(100dvh - 100px);
}

.profile-wrap {
    background-color: var(--palette-light);
    border-radius: 15px;
    position: relative;
    z-index: 20;
    padding: var(--m-space-48) var(--m-space-32);
    width: 100%;
    max-width: 860px;
    margin: 0 auto;
    box-shadow: 1px 1px 5px var(--palette-btn-shadow);
}

.profile-layout {
    width: 100%;
    max-width: 460px;
    margin: 0 auto;
}

h3 {
    text-align: center;
}

.user-email {
    cursor: pointer;
    display: block;
    text-align: center;
}

.user-data {
    margin-top: var(--m-space-48);
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--m-space-16);

    h4, .text-main {
        text-align: center;
    }
}

.playlist-btn {
    margin-top: var(--m-space-48);
    display: flex;
    justify-content: center;
}
</style>