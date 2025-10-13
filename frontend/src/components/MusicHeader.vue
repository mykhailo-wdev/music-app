<template>
    <header class="header-wrap">
        <div class="container">
            <div class="header-row">
                <div class="header-logo" v-if="route.path === '/'">
                    <img :src="require('@/assets/images/logo.svg')" alt="Logo" width="127" height="60" fetchpriority="high">
                </div>
                <div class="header-logo" v-else-if="route.path === '/player'">
                    <router-link to="playlists">
                        <svg width="172" height="32" viewBox="0 0 172 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="t2 d2">
                            <title id="t2">Your Playlists</title>
                            <g transform="translate(6,6)" stroke-width="2" stroke-linecap="round">
                                <path d="M0 20V10" stroke="#0D47A1">
                                <animate attributeName="d"
                                        values="M0 20V20; M0 20V6; M0 20V14; M0 20V10; M0 20V20"
                                        dur="1.2s" repeatCount="indefinite"/>
                                </path>
                                <path d="M6 20V6" stroke="#1565C0">
                                <animate attributeName="d"
                                        values="M6 20V20; M6 20V7; M6 20V14; M6 20V6; M6 20V20"
                                        dur="1s" repeatCount="indefinite"/>
                                </path>
                                <path d="M12 20V13" stroke="#1976D2">
                                <animate attributeName="d"
                                        values="M12 20V20; M12 20V8; M12 20V15; M12 20V13; M12 20V20"
                                        dur="0.9s" repeatCount="indefinite"/>
                                </path>
                                <path d="M18 20V8" stroke="#1E88E5">
                                <animate attributeName="d"
                                        values="M18 20V20; M18 20V6; M18 20V12; M18 20V8; M18 20V20"
                                        dur="1.4s" repeatCount="indefinite"/>
                                </path>
                                <path d="M24 20V15" stroke="#2196F3">
                                <animate attributeName="d"
                                        values="M24 20V20; M24 20V9; M24 20V14; M24 20V15; M24 20V20"
                                        dur="1.1s" repeatCount="indefinite"/>
                                </path>
                            </g>
                            <path d="M40 6V26" stroke="currentColor" stroke-width="1" opacity=".5"/>
                            <text x="48" y="21" font-size="16" font-family="Inter, system-ui, Segoe UI, Roboto, Arial"
                                    font-weight="900" letter-spacing=".2" fill="currentColor">Your Playlists</text>
                        </svg>
                    </router-link>
                </div>
                <div class="header-logo" v-else>
                    <img :src="require('@/assets/images/logo.svg')" alt="Logo" width="127" height="60" fetchpriority="high">
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

async function logout() {
    await store.dispatch('logout'); 
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
        height: 130px;
        .header-row {
            flex-direction: column;
        }
        .header-logo {
            width: auto;
            margin: 0 auto;
        }
        .header-btns {
            margin-top: 10px;
            gap: 10px;
        }
    }
}
</style>