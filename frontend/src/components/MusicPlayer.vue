<template>
    <div class="overlay" :class="{ close : !isActive }">
        <div class="btn-close" @click="closeModal">&times;</div>
        <div class="container">
            <div class="layout">
                <div class="player" v-if="currentTrack">
                    <img class="music-image" :src="currentTrack.album_image" :alt="currentTrack.track_name">
                    <div class="controls">
                        <div class="controls-second">
                            <div class="controls-item">
                                <svg class="controls-heart" role="button" aria-label="like current track" width="26" height="23" viewBox="0 0 26 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M25.9953 6.97384C25.9197 4.53874 24.7142 2.53726 23.0089 1.31365C20.8154 -0.261935 17.7912 -0.551012 15.2775 1.16801C14.4478 1.73734 13.6714 2.52513 13.0001 3.56007C12.3288 2.52513 11.5536 1.73734 10.7228 1.16911C8.20904 -0.551012 5.18597 -0.261935 2.99134 1.31475C1.2848 2.53837 0.0805383 4.53985 0.00490233 6.97494C-0.0364611 8.26807 0.176265 9.71125 0.93617 11.171C2.1298 13.4626 7.7375 18.5579 12.9564 22.9581L12.9977 23L13.0001 22.9978L13.0025 23L13.045 22.9581C18.2627 18.559 23.8692 13.4637 25.064 11.171C25.8251 9.71125 26.0355 8.26696 25.9953 6.97384Z" fill="currentColor"/>
                                </svg>
                            </div>
                            <div class="controls-item">
                                <svg @click="prev" class="controls-prev" role="button" aria-label="previous current track" width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M21.1613 22.878C21.9844 23.5025 23.1668 22.9154 23.1668 21.8821V4.45118C23.1668 3.41796 21.9844 2.83085 21.1613 3.45532L9.67274 12.1707C9.01338 12.671 9.01338 13.6622 9.67274 14.1625L21.1613 22.878ZM25.6668 21.8821C25.6668 24.9817 22.1198 26.7431 19.6503 24.8697L8.16177 16.1542C6.18371 14.6536 6.18371 11.6796 8.16178 10.179L19.6503 1.4636C22.1198 -0.4098 25.6668 1.35151 25.6668 4.45118V21.8821Z" fill="currentColor"/>
                                    <path d="M0.666504 1.91675C0.666504 1.2264 1.22615 0.666748 1.9165 0.666748C2.60685 0.666748 3.1665 1.2264 3.1665 1.91675V24.4167C3.1665 25.1071 2.60685 25.6667 1.9165 25.6667C1.22615 25.6667 0.666504 25.1071 0.666504 24.4167V1.91675Z" fill="currentColor"/>
                                </svg>
                            </div>
                            <div class="controls-item">
                                <svg @click="next" class="controls-next" role="button" aria-label="next current track" width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.8392 3.12177C4.01608 2.49727 2.8337 3.0844 2.8337 4.11764V21.5486C2.8337 22.5818 4.01608 23.1689 4.8392 22.5444L16.3277 13.829C16.9871 13.3288 16.9871 12.3375 16.3277 11.8373L4.8392 3.12177ZM0.333705 4.11764C0.333705 1.01802 3.8807 -0.743347 6.3502 1.13003L17.8387 9.84551C19.8168 11.3461 19.8168 14.3201 17.8387 15.8207L6.3502 24.5362C3.8807 26.4096 0.333705 24.6482 0.333705 21.5486V4.11764Z" fill="currentColor"/>
                                    <path d="M25.334 24.083C25.334 24.7734 24.7743 25.333 24.084 25.333C23.3936 25.333 22.834 24.7734 22.834 24.083V1.58304C22.834 0.892663 23.3936 0.333039 24.084 0.333039C24.7743 0.333039 25.334 0.892663 25.334 1.58304V24.083Z" fill="currentColor"/>
                                </svg>
                            </div>
                        </div>
                        <div class="controls-main">
                            <button @click="togglePlay" aria-label="Play/Pause">
                                <svg v-if="!playing" class="controls-play" width="75" height="75" viewBox="0 0 75 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M37.4998 75C57.9778 75 75 58.0146 75 37.5C75 17.0221 57.9411 0 37.4631 0C16.9484 0 0 17.0221 0 37.5C0 58.0146 16.9852 75 37.4998 75ZM37.4998 68.75C20.1469 68.75 6.28658 54.8529 6.28658 37.5C6.28658 20.1838 20.1102 6.25003 37.4631 6.25003C54.7792 6.25003 68.713 20.1838 68.75 37.5C68.7868 54.8529 54.816 68.75 37.4998 68.75ZM31.0293 51.0294L50.6984 39.4118C52.1321 38.5295 52.0954 36.5074 50.6984 35.6986L31.0293 24.0073C29.522 23.125 27.5366 23.8235 27.5366 25.4779V49.5588C27.5366 51.2499 29.4115 51.9853 31.0293 51.0294Z" fill="var(--palette-white)"/>
                                </svg>
                                <svg v-else class="controls-play" width="75" height="75" viewBox="0 0 75 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_237_2)">
                                    <path d="M37.5 0C16.7884 0 0 16.7884 0 37.5C0 58.2116 16.7884 75 37.5 75C58.2116 75 75 58.2116 75 37.5C75 16.7884 58.2116 0 37.5 0ZM37.5 69.1406C20.0249 69.1406 5.85938 54.9751 5.85938 37.5C5.85938 20.0249 20.0249 5.85938 37.5 5.85938C54.9751 5.85938 69.1406 20.0249 69.1406 37.5C69.1406 54.9751 54.9751 69.1406 37.5 69.1406Z" fill="var(--palette-white)"/>
                                    <path d="M39.7705 49.292H49.1455V30.9814V25.8545H39.7705V49.292Z" fill="var(--palette-white)"/>
                                    <path d="M35.083 25.8545H25.708V49.292H35.083V25.8545Z" fill="var(--palette-white)"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0_237_2">
                                    <rect width="75" height="75" fill="white"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-info">
                            <div class="info-left">
                                <p class="music-autor">{{ currentTrack.artist_name }}</p>
                                <p class="music-name">{{ currentTrack.track_name }}</p>
                            </div>
                            <div class="info-right">
                                <p class="music-time">{{ formatTime(duration) }}</p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <input class="progress-track" type="range" min="0" :max="duration" step="0.1" v-model="currentTime" @input="seek">
                        </div>
                        <div class="progress-time">
                            <p class="time-curr">{{ formatTime(currentTime) }}</p>
                            <div class="controls-bottom">
                                <div class="controls-item">
                                    <svg @click="toggleRepeat" :class="{ active: repeat }"  class="controls-repeat" role="button" aria-label="repeat current track" width="26" height="22" viewBox="0 0 26 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M24.7036 8.15304C25.4412 8.15304 25.8456 7.74717 25.8456 6.9832V1.4325C25.8456 0.572978 25.2985 0 24.4659 0C23.7641 0 23.3598 0.226797 22.8007 0.656556L21.2782 1.83832C21.0046 2.0532 20.8976 2.29191 20.8976 2.55456C20.8976 2.93655 21.1831 3.25884 21.6707 3.25884C21.8609 3.25884 22.0515 3.19915 22.2178 3.05588L23.4428 2.06511H23.538V6.9832C23.538 7.74717 23.9543 8.15304 24.7036 8.15304ZM0 10.1943C0 10.9224 0.582833 11.5073 1.30837 11.5073C2.04578 11.5073 2.62856 10.9224 2.62856 10.1943V9.40641C2.62856 7.5084 3.925 6.25503 5.87563 6.25503H12.2983V8.74985C12.2983 9.39444 12.7146 9.80031 13.3688 9.80031C13.6542 9.80031 13.9516 9.6929 14.1775 9.50191L18.6735 5.77755C19.2087 5.33587 19.1968 4.6316 18.6735 4.178L14.1775 0.429759C13.9516 0.250684 13.6542 0.131352 13.3688 0.131352C12.7146 0.131352 12.2983 0.549142 12.2983 1.19373V3.66466H6.12536C2.39066 3.66466 0 5.82527 0 9.21542V10.1943ZM11.1446 13.3934C11.1446 12.7488 10.7402 12.3429 10.086 12.3429C9.80058 12.3429 9.50325 12.4503 9.27728 12.6294L4.78136 16.3538C4.24613 16.7954 4.24613 17.4997 4.78136 17.9533L9.27728 21.7015C9.50325 21.8925 9.80058 22 10.086 22C10.7402 22 11.1446 21.5941 11.1446 20.9495V18.4547H19.8747C23.6094 18.4547 26 16.2821 26 12.904V11.9251C26 11.185 25.4173 10.6001 24.68 10.6001C23.9543 10.6001 23.3714 11.185 23.3714 11.9251V12.713C23.3714 14.599 22.075 15.8644 20.1245 15.8644H11.1446V13.3934Z" fill="currentColor"/>
                                    </svg>
                                </div>
                                <div class="controls-item">
                                    <svg class="controls-random" @click="toggleRandom" :class="{ active: random }" role="button" aria-label="random track" width="27" height="22" viewBox="0 0 27 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8.73333 14.1221C7.72222 14.8444 6.42222 15.2777 5.12222 15.2777H1.94444C1.07778 15.2777 0.5 15.8555 0.5 16.7221C0.5 17.5888 1.07778 18.1666 1.94444 18.1666H5.12222C7 18.1666 8.87778 17.5888 10.4667 16.4333C11.0444 15.9999 11.1889 14.9888 10.7556 14.411C10.1778 13.6888 9.31111 13.5444 8.73333 14.1221Z" fill="currentColor"/>
                                        <path d="M14.9443 9.06659C15.3776 9.06659 15.8109 8.92214 16.0998 8.63325C17.1109 7.33325 18.6998 6.61103 20.4332 6.61103H21.5887L21.1554 7.04436C20.5776 7.62214 20.5776 8.48881 21.1554 9.06659C21.4443 9.35547 21.8776 9.49992 22.1665 9.49992C22.4554 9.49992 22.8887 9.35547 23.1776 9.06659L26.0665 6.1777C26.2109 6.03325 26.3554 5.88881 26.3554 5.74436C26.4998 5.45547 26.4998 5.02214 26.3554 4.58881C26.2109 4.44436 26.2109 4.29992 26.0665 4.15547L23.1776 1.26659C22.5998 0.688808 21.7332 0.688808 21.1554 1.26659C20.5776 1.84436 20.5776 2.71103 21.1554 3.28881L21.5887 3.72214H20.4332C17.8332 3.72214 15.522 4.8777 13.7887 6.75548C13.2109 7.33325 13.3554 8.19992 13.9332 8.7777C14.222 9.06659 14.6554 9.06659 14.9443 9.06659Z" fill="currentColor"/>
                                        <path d="M26.0667 15.7111L23.1778 12.8222C22.6 12.2444 21.7333 12.2444 21.1556 12.8222C20.5778 13.3999 20.5778 14.2666 21.1556 14.8444L21.5889 15.2777H19.4222C17.1111 15.2777 15.2333 13.9777 14.2222 11.9555L12.4889 8.48883C11.0444 5.59995 8.01111 3.72217 4.68889 3.72217H1.94444C1.07778 3.72217 0.5 4.29995 0.5 5.16661C0.5 6.03328 1.07778 6.61106 1.94444 6.61106H4.68889C7 6.61106 8.87778 7.91106 9.88889 9.93328L11.6222 13.3999C13.0667 16.4333 16.1 18.3111 19.4222 18.3111H21.5889L21.1556 18.7444C20.5778 19.3222 20.5778 20.1888 21.1556 20.7666C21.4444 21.0555 21.8778 21.1999 22.1667 21.1999C22.4556 21.1999 22.8889 21.0555 23.1778 20.7666L26.0667 17.8777C26.6444 17.1555 26.6444 16.2888 26.0667 15.7111Z" fill="currentColor"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <audio ref="audio" :src="currentTrack?.audio_url"></audio>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useStore } from 'vuex';
import { nextTick } from 'vue';

const store = useStore();

const isActive = computed(() => store.state.player.visible);

const currentTime = ref(0);
const duration = ref(0);
const audio = ref(null);

const playing = computed(() => store.state.player.playing);
const playlist = computed(() => store.state.player.currentPlaylist);
const index = computed(() => store.state.player.currentTrackIndex);
const currentTrack = computed(() => playlist.value[index.value]);
const repeat = computed(() => store.state.player.repeat);
const random = computed(() => store.state.player.random);

function closeModal() {
    store.commit('setVisible', false);
    store.commit('setPlaying', false);
}

function togglePlay() {
    store.dispatch('togglePlay');
}

function getRandomIndex() {
    if (playlist.value.length <= 1) return index.value;
    let randomIndex = Math.floor(Math.random() * playlist.value.length);
    while (randomIndex === index.value) {
        randomIndex = Math.floor(Math.random() * playlist.value.length);
    }
    return randomIndex;
}

function next() {
    if (random.value) {
        store.commit('setTrackIndex', getRandomIndex());
    } else {
        store.commit('nextTrack');
    }
    store.commit('setPlaying', true);
}

function prev() {
    if (random.value) {
        store.commit('setTrackIndex', getRandomIndex());
    } else {
        store.commit('prevTrack');
    }
    store.commit('setPlaying', true);
}

function toggleRepeat() {
    store.commit('toggleRepeat');
}

function seek() {
    if (audio.value) {
        audio.value.currentTime = currentTime.value;
    }
}

function toggleRandom() {
    store.commit('toggleRandom');
}

watch(currentTrack, async () => {
    if (!audio.value) return;
    await nextTick();
    try {
        await audio.value.play();
    } catch(err) {
        if (err.name !== 'AbortError') {
            console.warn("Play interrupted:", err);
        }
    }
});


function formatTime(sec) {
    const minutes = Math.floor(sec / 60) || 0;
    const seconds = Math.floor(sec % 60) || 0;
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

watch(playing, (val) => {
    if (!audio.value) return; 
    const action = val ? 'play' : 'pause';
    const result = audio.value[action]();
    if (result && typeof result.catch === 'function') {
        result.catch(err => {
            if (err.name !== 'AbortError') console.warn(err);
        });
    }
});



onMounted(async () => {
    await nextTick(); 
    if (!audio.value) return;
    audio.value.addEventListener('timeupdate', () => {
        currentTime.value = audio.value.currentTime;
    });
    audio.value.addEventListener('loadedmetadata', () => {
        duration.value = audio.value.duration;
    });
    audio.value.addEventListener('ended', () => {
        if (store.state.player.repeat) {
            audio.value.currentTime = 0;
            audio.value.play();
        } else {
            store.commit('nextTrack');
        }
    });
});
</script>

<style lang="scss" scoped>
@use '../assets/styles/mixins';
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    background-color: var(--palette-light);
    overflow: hidden;
}
.container {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}

.btn-close {
    cursor: pointer;
    position: absolute;
    right: 5%;
    top: 5%;
    font-size: 24px;
    font-weight: 600;
    color: var(--palette-dark);
}

.close {
    display: none;
}

.controls-repeat.active, 
.controls-random.active {
    color: var(--palette-violet);
}

.layout {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100vh;
    height: 100dvh;
}

.player {
    width: 100%;
    background-color: var(--palette-silver);
    border-radius: 15px;
    box-shadow: 0px 15px 35px -5px rgba(50, 88, 130, 0.32);
    position: relative;
    padding: 160px 16px 16px;
}

.music-image {
    object-fit: cover;
    object-position: center;
    width: 80%;
    position: absolute;
    top: -70px;
    left: 50%;
    transform: translate(-50%, 0);
    z-index: 20;
    border-radius: 15px;
    overflow: hidden;
    height: 230px;
    box-shadow: 0px 10px 40px 0px rgba(76, 70, 124, 0.5);
}

.controls {
    margin-top: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.controls-second {
    display: grid;
    grid-template-columns: repeat(3, 26px);
    gap: 24px;
}

.music-autor, .music-name, .music-time, .time-curr {
    @include mixins.body-text();
    font-weight: 700;
}
.music-name, .music-time {
    margin-top: 8px;
    color: var(--palette-violet);
}

.music-time {
    text-align: right;
}

.time-curr {
    margin-top: 8px;
    color: var(--palette-dark);
}

.progress-track {
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

.controls-play, .controls-heart, .controls-next, .controls-prev, .controls-repeat, .controls-random {
    cursor: pointer;
    display: block;
}

.controls-item {
    transition: all 0.3s ease-in-out;
    transform-origin: center;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    position: relative;
    &::before {
        content: "";
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background-color: var(--palette-white);
        transform: scale(0.5);
        opacity: 0;
        box-shadow: 0px 5px 10px 0px rgba(76, 70, 124, 0.2);
        transition: all 0.3s ease-in-out;
        transition: all 0.4s  cubic-bezier(0.35, 0.57, 0.13, 0.88);
    }
    &:hover {
        border-radius: 50%;
        transform-origin: center;
        &::before {
            opacity: 1;
            transform: scale(1.3);
        }
    }
}

.controls-heart, .controls-next, .controls-prev, .controls-repeat, .controls-random {
    width: 26px;
    height: 26px;
    color: #0F0F0F;
    transition: all 0.3s ease-in-out;
    position: relative;
    z-index: 10;
}

.controls-play {
    width: 75px;
    height: 75px;
    filter: drop-shadow(0 11px 6px rgba(172, 184, 204, 0.45));
}

.progress-time {
    margin-top: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.controls-bottom {
    display: grid;
    grid-template-columns: repeat(2, 40px);
    gap: 16px;
    align-items: center;
}

</style>