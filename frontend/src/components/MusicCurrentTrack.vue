<template>
    <div class="current-track" v-if="currentSong">
        <h4>{{ currentSong.name }}</h4>
        <p class="body-text">{{ currentSong.artist_name }}</p>
        <div class="loader-wrap" v-if="isLoadingImg">
            <div class="loader"></div>
        </div>
        <img 
            v-else class="current-album" 
            :src="imgSrc"
            alt="Обкладинка альбому" 
            @load="preload"
            />
        <div class="rock-player">
            <button @click="togglePlay" aria-label="Play/Pause" class="item-play">
                <svg v-if="!isPlaying"  width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 12C22 14.7578 20.8836 17.2549 19.0782 19.064M2 12C2 9.235 3.12222 6.73208 4.93603 4.92188M19.1414 5.00003C19.987 5.86254 20.6775 6.87757 21.1679 8.00003M5 19.1415C4.08988 18.2493 3.34958 17.1845 2.83209 16" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16.2849 8.04397C17.3458 9.05877 18 10.4488 18 11.9822C18 13.5338 17.3302 14.9386 16.2469 15.9564M7.8 16C6.68918 14.9789 6 13.556 6 11.9822C6 10.4266 6.67333 9.01843 7.76162 8" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M13.6563 10.4511C14.5521 11.1088 15 11.4376 15 12C15 12.5624 14.5521 12.8912 13.6563 13.5489C13.4091 13.7304 13.1638 13.9014 12.9384 14.0438C12.7407 14.1688 12.5168 14.298 12.2849 14.4249C11.3913 14.914 10.9444 15.1586 10.5437 14.8878C10.1429 14.617 10.1065 14.0502 10.0337 12.9166C10.0131 12.596 10 12.2817 10 12C10 11.7183 10.0131 11.404 10.0337 11.0834C10.1065 9.94977 10.1429 9.38296 10.5437 9.1122C10.9444 8.84144 11.3913 9.08599 12.2849 9.57509C12.5168 9.70198 12.7407 9.83123 12.9384 9.95619C13.1638 10.0986 13.4091 10.2696 13.6563 10.4511Z" stroke="#1C274C" stroke-width="1.5"/>
                </svg>
                <svg v-else width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.5" d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" fill="#1C274C"/>
                    <path d="M8.07612 8.61732C8 8.80109 8 9.03406 8 9.5V14.5C8 14.9659 8 15.1989 8.07612 15.3827C8.17761 15.6277 8.37229 15.8224 8.61732 15.9239C8.80109 16 9.03406 16 9.5 16C9.96594 16 10.1989 16 10.3827 15.9239C10.6277 15.8224 10.8224 15.6277 10.9239 15.3827C11 15.1989 11 14.9659 11 14.5V9.5C11 9.03406 11 8.80109 10.9239 8.61732C10.8224 8.37229 10.6277 8.17761 10.3827 8.07612C10.1989 8 9.96594 8 9.5 8C9.03406 8 8.80109 8 8.61732 8.07612C8.37229 8.17761 8.17761 8.37229 8.07612 8.61732Z" fill="#1C274C"/>
                    <path d="M13.0761 8.61732C13 8.80109 13 9.03406 13 9.5V14.5C13 14.9659 13 15.1989 13.0761 15.3827C13.1776 15.6277 13.3723 15.8224 13.6173 15.9239C13.8011 16 14.0341 16 14.5 16C14.9659 16 15.1989 16 15.3827 15.9239C15.6277 15.8224 15.8224 15.6277 15.9239 15.3827C16 15.1989 16 14.9659 16 14.5V9.5C16 9.03406 16 8.80109 15.9239 8.61732C15.8224 8.37229 15.6277 8.17761 15.3827 8.07612C15.1989 8 14.9659 8 14.5 8C14.0341 8 13.8011 8 13.6173 8.07612C13.3723 8.17761 13.1776 8.37229 13.0761 8.61732Z" fill="#1C274C"/>
                </svg>
            </button>

            <span class="item-currtime">{{ formattedCurrentTime }}</span>
            <input
                class="item-dur"
                type="range"
                min="0"
                :max="duration"
                step="0.1"
                v-model="currentTime"
                @input="seekAudio"
            />
            <span class="item-generaltime">{{ formattedDuration }}</span>
            <input
                class="item-volume"
                type="range"
                min="0"
                max="1"
                step="0.01"
                v-model="volume"
                @input="updateVolume"
            />
        </div>
        <audio ref="audio" :src="currentSong.audio" @timeupdate="updateTime" @loadedmetadata="loadMetadata" />
        <div class="playlist-btn" v-if="route.path === '/player'">
            <music-add-to-playlist></music-add-to-playlist>
        </div>
    </div>
</template>

<script setup>
import MusicAddToPlaylist from './MusicAddToPlaylist.vue';
import { useStore } from 'vuex';
import { ref, watch, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const store = useStore();
const isLoadingImg = ref(true); 
const imgSrc = ref('');
const audio = ref(null);
const isPlaying = ref(false);
const currentTime = ref(0);
const duration = ref(0);
const volume = ref(1);

const router = useRouter();
const route = useRoute();

const currentSong = computed(() => store.getters.currentSong);

watch(currentSong, (song) => {
  if (!song) return;



  isLoadingImg.value = true;
  imgSrc.value = ''; 

  const image = new Image();
  image.src = song.album_image;

  image.onload = () => {
    setTimeout(() => {
        imgSrc.value = song.album_image;
        isLoadingImg.value = false;
    }, 800);
  };

    image.onerror = () => {
        console.warn('Не вдалося завантажити обкладинку');
        isLoadingImg.value = false;
    };
}, { immediate: true });

watch(currentSong, () => {
    if (audio.value) {
        audio.value.pause();
        audio.value.currentTime = 0;
        isPlaying.value = false;
    }
});

const formattedTime = (sec) => {
    const minutes = Math.floor(sec / 60);
    const seconds = Math.floor(sec % 60);
    return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
};

const formattedCurrentTime = computed(() => formattedTime(currentTime.value));
const formattedDuration = computed(() => formattedTime(duration.value));

const togglePlay = () => {
    if (!audio.value) return;
    if (audio.value.paused) {
        audio.value.play();
        isPlaying.value = true;
    } else {
        audio.value.pause();
        isPlaying.value = false;
    }
};

const updateTime = () => {
    if (!audio.value) return;
    currentTime.value = audio.value.currentTime;
};

const seekAudio = () => {
    if (!audio.value) return;
    audio.value.currentTime = currentTime.value;
};

const updateVolume = () => {
    if (!audio.value) return;
    audio.value.volume = volume.value;
};

const loadMetadata = () => {
    if (!audio.value) return;
    duration.value = audio.value.duration;
    audio.value.volume = volume.value;
};
</script>

<style lang="scss" scoped>
@use '../assets/styles/mixins' as*;
.current-track {
    margin-top: var(--m-space-16);
    overflow: hidden;
    padding: 8px;
    audio {
        width: 100%;
    }
}
h4 {
    text-align: center;
}
.body-text {
    text-align: center;
} 
.current-album {
    margin: var(--m-space-16) auto 0;
    width: 150px;
    height: 150px;
    object-fit: contain;
    object-position: center;
}
.rock-player {
    margin-top: var(--m-space-16);
    display: grid;
    grid-template-columns: 20px 40px 1fr 40px 80px;
    align-content: center;
    align-items: center;
    gap: 8px;
    background-color: var(--palette-light);
    border-radius: 30px;
    padding: 10px 20px;
    box-shadow: 0 0 10px var(--palette-darkred);
    button {
        background: none;
        border: none;
        cursor: pointer;
        display: flex;
        flex-shrink: 0;
        width: 24px;
        height: 24px;
    }
    input[type="range"] {
        appearance: none;
        -webkit-appearance: none;
        background: transparent;
        width: 100%;

        &::-webkit-slider-runnable-track {
            height: 4px;
            background: var(--palette-violet);
            border-radius: 2px;
        }
        &::-webkit-slider-thumb {
            appearance: none;
            width: 12px;
            height: 12px;
            background: var(--palette-darkviolet);
            border-radius: 50%;
            cursor: pointer;
            margin-top: -4px; 
            opacity: 1;
        }
        &::-moz-range-track {
            height: 4px;
            background: var(--palette-violet);
            border-radius: 2px;
        }
        &::-moz-range-thumb {
            width: 12px;
            height: 12px;
            background: var(--palette-darkviolet);
            border-radius: 50%;
            cursor: pointer;
            opacity: 1;
        }
        &::-ms-track {
            height: 4px;
            background: transparent;
            border-color: transparent;
            color: transparent;
        }
    }
    span {
        font-size: 12px;
        min-width: 40px;
        text-align: center;
    }
}
.loader-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 150px;
}
.loader {
    margin-top: var(--m-space-16);
    width: 40px;
    aspect-ratio: .75;
    --c: no-repeat linear-gradient(var(--palette-darkviolet) 0 0);
    background: 
        var(--c) 0%   50%,
        var(--c) 50%  50%,
        var(--c) 100% 50%;
    background-size: 20% 50%;
    animation: l6 1s infinite linear;
}
@keyframes l6 {
    20% {background-position: 0% 0%  ,50% 50% ,100% 50% }
    40% {background-position: 0% 100%,50% 0%  ,100% 50% }
    60% {background-position: 0% 50% ,50% 100%,100% 0%  }
    80% {background-position: 0% 50% ,50% 50% ,100% 100%}
}

.playlist-btn {
    margin-top: var(--m-space-16);
}

@media(max-width: 576px) {
    .current-track {
        margin-top: 0;
    }
    .rock-player {
        margin-top: 8px;
        grid-template-columns: 30px 40px 1fr 1fr;
        grid-template-rows: auto;
        grid-template-areas: 
        "item-play item-dur item-dur item-dur"
        "item-currtime item-generaltime item-volume ."
        ;
        border-radius: 8px;
        gap: 20px 8px;
    }
    .item-play {
        grid-area: item-play;
    }
    .item-currtime {
        grid-area: item-currtime;
    }
    .item-dur {
        grid-area: item-dur;
    }
    .item-generaltime {
        grid-area: item-generaltime;
    }
    .item-volume {
        grid-area: item-volume;
    }
    .loader-wrap {
        height: 80px;
    }
    .loader {
        margin-top: 8px;
        width: 80px;
    }
    .current-album {
        margin: 8px auto 0;
        width: 80px;
        height: 80px;
        object-fit: contain;
        object-position: center;
    }
}
</style>