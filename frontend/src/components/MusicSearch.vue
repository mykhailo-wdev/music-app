<template>
    <div class="search-wrap">
        <transition name="slide-fade">
            <music-alert
                v-if="showAlert"
                alertTitle="Ліміт вичерпано"
                alertDescription="Будь ласка, зараєструйтесь, щоб користуватись безлімітним пошуком. А також отримати більше спектр можливостей"
                @close="closeAlert"
            ></music-alert>
        </transition>
        <div class="search-row">
            <input 
                class="search-text" 
                type="text" 
                placeholder="Введіть назву пісні"
                v-model="searchValue"
                @focus="onFocus"
                @keydown.down.prevent="highlightNext"
                @keydown.up.prevent="highlightPrev"
                @keydown.enter.prevent="selectHighlighted"
                autocomplete="off"
            />
            <music-button  
                type-btn="btn-sky" 
                text="Пошук" 
                :disabled="isLoading" 
                @action="searchMusic">
            </music-button>
        </div>

        <ul v-if="isDropdownVisible && songs.length" class="dropdown">
            <li
                v-for="(song, index) in songs"
                :key="song.id"
                :class="{ highlighted: index === highlightedIndex }"
                @mousedown.prevent="selectSong(song)"
            >
                {{ song.name }} – {{ song.artist_name }}
            </li>
        </ul>

        <music-current-track></music-current-track>
        
    </div>
</template>

<script setup>
import MusicButton from './MusicButton.vue';
import MusicCurrentTrack from './MusicCurrentTrack.vue';
import MusicAlert from './MusicAlert.vue';
import { useStore } from 'vuex';
import { ref, watch, computed } from 'vue';

const store = useStore();

const searchValue = ref('');
const highlightedIndex = ref(-1);
const isDropdownVisible = ref(false);
const isLoading = ref(false);
const isSongSelected = ref(false);

const props = defineProps({
  searchLimit: {
    type: Number,
    default: 3
  }
});

const songs = computed(() => store.getters.songMatch);
const currentSong = computed(() => store.getters.currentSong);

const onFocus = () => {
    isSongSelected.value = false;
    if (songs.value.length) {
        isDropdownVisible.value = true;
    }
};

watch(searchValue, async (newValue) => {
    if (newValue.length > 1) {
        isLoading.value = true;
        store.commit('getInputValue', newValue);
        await store.dispatch('fetchSongs');
        isLoading.value = false;
        if (!isSongSelected.value && songs.value.length) {
            isDropdownVisible.value = true;
        }
        highlightedIndex.value = -1;
    } else {
        isDropdownVisible.value = false;
    }
});

const searchMusic = async () => {
    if (searchValue.value.length > 1) {
        isLoading.value = true;
        store.commit('getInputValue', searchValue.value);
        await store.dispatch('fetchSongs');
        
        store.commit('calculateSearch');

        if (props.searchLimit && count.value >= props.searchLimit) {
            showAlert.value = true;
            isLoading.value = false;
            return;
        }

        isLoading.value = false;
        isDropdownVisible.value = songs.value.length > 0;
        highlightedIndex.value = -1;
    }
};


const selectSong = (song) => {
    if (props.searchLimit && count.value >= props.searchLimit) {
        showAlert.value = true;
        return;
    }

    store.commit('calculateSearch');

    searchValue.value = `${song.name} – ${song.artist_name}`;
    store.commit('setCurrentSong', song);
    isDropdownVisible.value = false;
    isSongSelected.value = true;
    searchValue.value = '';
};


const highlightNext = () => {
    if (highlightedIndex.value < songs.value.length - 1) {
        highlightedIndex.value++;
    }
};

const highlightPrev = () => {
    if (highlightedIndex.value > 0) {
        highlightedIndex.value--;
    }
};

const selectHighlighted = () => {
    if (highlightedIndex.value >= 0 && songs.value[highlightedIndex.value]) {
        selectSong(songs.value[highlightedIndex.value]);
    }
};

const showAlert = ref(false);
const count = computed(() => store.getters.countSearch);

watch(count, (newVal) => {
    if (props.searchLimit && newVal >= props.searchLimit) {
            showAlert.value = true;
    }
});

function closeAlert() {
    showAlert.value = false;
}



</script>

<style lang="scss" scoped>
@use '../assets/styles/mixins' as*;

.search-wrap {
    width: 100%;
    max-width: 768px;
    margin: 0 auto;
    position: relative;

    .search-row {
        margin-top: var(--m-space-32);
        display: grid;
        grid-template-columns: 1.7fr 0.3fr;
        gap: 4px;
    }
    .search-text {
        display: inline-block;
        width: 100%;
        height: 40px;
        border: 1px solid var(--palette-stroke);
        border-radius: 8px;
        padding: 0 var(--m-space-16);
        @include body-text;
        &:focus {
            outline: none;
        }
        &:focus::-webkit-input-placeholder {
            color: transparent;
        }
        &:focus::-moz-placeholder {
            color: transparent;
        }
        &:focus:-moz-placeholder {
            color: transparent;
        }
        &:focus:-ms-input-placeholder {
            color: transparent;
        }
    }
    .dropdown {
        position: absolute;
        background: var(--palette-white);
        border: 1px solid var(--palette-stroke);
        max-height: 180px;
        overflow-y: auto;
        width: 100%;
        padding: 0;
        list-style: none;
        z-index: 10;
        top: 42px;
        border-radius: 8px;
    }
    .dropdown li {
        padding: 8px 12px;
        cursor: pointer;
    }
    .dropdown li.highlighted,
    .dropdown li:hover {
        background-color: var(--palette-sky);
        color: var(--palette-white);
    }
    


}


</style>