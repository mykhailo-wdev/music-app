<template>
    <main class="main-app">
        <div class="container">
            <section class="profile">
                <div class="profile-wrap">
                    <div class="profile-layout">
                        <h3>Vincent Sharp 55</h3>
                        <a href="mailto:mikhail_l@stackcraft.tech" class="text-main user-email">mikhail322@gmail.com</a>
                        <ul class="user-data">
                            <li class="user-qty-folders">
                                <h4>50</h4>
                                <p class="text-main">Папок</p>
                            </li>
                            <li class="user-qty-tracks">
                                <h4>44</h4>
                                <p class="text-main">Треків</p>
                            </li>
                            <li class="user-qty-favorites">
                                <h4>20</h4>
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