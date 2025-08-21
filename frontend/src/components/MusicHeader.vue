<template>
    <header class="header-wrap">
        <div class="container">
            <div class="header-row">
                <div class="header-logo" v-if="route.path === '/'">
                    <img :src="require('@/assets/images/logo.svg')" alt="Logo" width="127" height="60">
                </div>
                <div class="header-logo" v-else-if="route.path === '/player'">
                    <router-link to="playlists">
                        <img :src="require('@/assets/images/playlists.svg')" alt="Playlists" width="120" height="26">
                    </router-link>
                </div>
                <div class="header-logo" v-else>
                    <img :src="require('@/assets/images/logo.svg')" alt="Logo" width="127" height="60">
                </div>
                <div class="header-btns" v-if="isOnPlayerRoute">
                    <music-button type-btn="btn-fresh" text="Вихід" @click="logout"></music-button>
                    <music-button type-btn="btn-sky" text="Профіль" to="/profile"></music-button>
                </div>
                <div class="header-btns" v-else-if="isOnProfileRoute">
                    <music-button type-btn="btn-sky" text="Назад в плеєр" to="/player"></music-button>
                </div>
                <div class="header-btns" v-else-if="isOnPlayListsRoute">
                    <music-button type-btn="btn-fresh" text="Вихід" @click="logout"></music-button>
                    <music-button type-btn="btn-sky" text="Назад в плеєр" to="/player"></music-button>
                </div>
                <div class="header-btns" v-else>
                    <music-button type-btn="btn-fresh" text="Вхід" to="/login"></music-button>
                    <music-button type-btn="btn-sky" text="Реєстрація" to="/register"></music-button>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import MusicButton from './MusicButton.vue';
import { useRouter, useRoute } from 'vue-router';
import { computed } from 'vue';
import { useStore } from 'vuex';

const router = useRouter();
const route = useRoute();
const store = useStore();

const isOnPlayerRoute = computed(() => {
  return route.path.startsWith('/player');
});

const isOnProfileRoute = computed(() => {
  return route.path.startsWith('/profile');
});

const isOnPlayListsRoute = computed(() => {
  return route.path.startsWith('/playlists');
});

function logout() {
  store.dispatch('logout');
  router.push('/login');
}

</script>

<style lang="scss">
.header-wrap {
    height: 100px;
    display: flex;
    align-items: center;
    background-color: var(--palette-light);
    border-bottom: 1px solid var(--palette-violet);

    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .header-btns {
        display: grid;
        grid-template-columns: auto auto;
        gap: 20px;
    }
}

@media (max-width: 576px) {
    .header-wrap {
        height: 120px;
        .header-row {
            flex-direction: column;
        }
        .header-logo {
            width: 100%;
            max-width: 80px;
            margin: 0 auto;
        }
        .header-btns {
            margin-top: 10px;
            gap: 10px;
        }
    }
}
</style>