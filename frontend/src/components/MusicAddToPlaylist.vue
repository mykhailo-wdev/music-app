<template>
    <div>
        <music-button type-btn="btn-sky" text="Додати в плейлист" @action="open"></music-button>

        <div v-if="openModal" class="modal">
            <div class="modal-body">
                <div class="close-row">
                    <span @click="closeModal">✖</span>
                </div>
                <h4>Додати до плейлиста</h4>

                <div v-if="!playlists.length" class="empty">
                    <p>У вас ще немає плейлистів.</p>
                    <input class="first-playlist" v-model="newName" placeholder="Назва нового плейлиста" />
                    <music-button :disabled="!newName.trim()" type-btn="btn-sky" text="Створити" @action="create"></music-button>
                </div>

                <div v-else>
                    <label class="lbl">Оберіть плейлист:</label>
                    <ul class="pl-list">
                        <li v-for="pl in playlists" :key="pl.id">
                        <label class="radio-label">
                            <input class="radio-input" type="radio" name="pl" :value="pl.id" v-model="selectedId" />
                            <span class="radio-custom"></span>
                            {{ pl.name }}
                        </label>
                        </li>
                    </ul>

                    <details class="create-box">
                        <summary class="create-folder">
                            <svg width="21px" height="21px" viewBox="0 -0.5 21 21" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="Dribbble-Light-Preview" transform="translate(-259.000000, -600.000000)" fill="var(--palette-black)">
                                        <g id="icons" transform="translate(56.000000, 160.000000)">
                                            <path d="M214.55,449 L217.7,449 L217.7,451 L214.55,451 L214.55,454 L212.45,454 L212.45,451 L209.3,451 L209.3,449 L212.45,449 L212.45,446 L214.55,446 L214.55,449 Z M213.5,458 C208.86845,458 205.1,454.411 205.1,450 C205.1,445.589 208.86845,442 213.5,442 C218.13155,442 221.9,445.589 221.9,450 C221.9,454.411 218.13155,458 213.5,458 L213.5,458 Z M213.5,440 C207.70085,440 203,444.477 203,450 C203,455.523 207.70085,460 213.5,460 C219.29915,460 224,455.523 224,450 C224,444.477 219.29915,440 213.5,440 L213.5,440 Z" id="plus_circle-[#1425]">
                                        </path>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                            <span class="newfolder-name">Створити новий</span>
                        </summary>
                        <div class="row">
                            <input class="newfolder-input" v-model="newName" placeholder="Назва нового плейлиста" />
                            <music-button :disabled="!newName.trim()" type-btn="btn-sky" text="Створити" @action="create"></music-button>
                        </div>
                    </details>

                    <div class="actions">
                        <music-button type-btn="btn-fresh" text="Скасувати" @action="close"></music-button>
                        <music-button :disabled="!selectedId" type-btn="btn-sky" text="Додати" @action="add"></music-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import MusicButton from './MusicButton.vue';
import { computed, onMounted, ref } from 'vue';
import { useStore } from 'vuex';

const store = useStore();

const openModal = ref(false);
const selectedId = ref(null);
const newName = ref('');

const playlists = computed(() => store.getters['playlists']);
const currentSong = computed(() => store.getters['currentSong']); 

function open() {
    openModal.value = true;
    store.dispatch('fetchPlaylists');
}
function close() {
    openModal.value = false;
    selectedId.value = null;
    newName.value = '';
}
async function create() {
    if (!newName.value.trim()) return;
        try {
            await store.dispatch('createPlaylist', newName.value.trim());
            const last = playlists.value[0];
            if (last) selectedId.value = last.id;
            newName.value = '';
        } catch(e) { 
            alert(e.message); 
        }
    }
    async function add() {
        if (!selectedId.value || !currentSong.value) return;
        try {
            await store.dispatch('addTrackToPlaylist', { playlistId: selectedId.value, song: currentSong.value });
            closeModal();
            } catch(e) { alert(e.message); 
        }
    }

onMounted(() => {
    store.dispatch('fetchPlaylists');
});

function closeModal() {
    openModal.value = false;
    selectedId.value = null;
    newName.value = '';
}
</script>

<style lang="scss" scoped>
@use '../assets/styles/mixins';
.modal { 
    position: fixed; 
    inset:0; 
    background: var(--palette-bg-modal); 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    z-index: 1000; 
}
.modal-body{ 
    background:var(--palette-white); 
    padding: var(--m-space-16); 
    border-radius: 12px; 
    width: 100%;
    max-width: 420px;
}
.close-row {
    cursor: pointer;
    display: flex;
    justify-content: flex-end;
    span {
        font-size: 16px;
        font-weight: 700;
    }
}
.pl-list{ 
    list-style: none; 
    padding: 0; 
    margin: var(--m-space-16); 
    max-height: 260px; 
    overflow: auto; 
}
.lbl{ 
    font-weight: 600; 
}
.actions{ 
    display: flex; 
    gap: 8px; 
    justify-content: flex-end; 
    margin-top: 12px; 
}
.create-box .row { 
    display: flex; 
    gap: 8px; 
    margin-top: 8px; 
}
.empty input { 
    width: 100%; 
    margin: .5rem 0; 
}
.create-folder {
    display: flex;
    align-items: center;
    &::marker {
        display: none;
    }
}
.newfolder-name {
    padding-left: 8px;
    cursor: pointer;
}
.newfolder-input {
    border: 1px solid var(--palette-dark);
    text-indent: 8px;
    @include mixins.text-small();
}
.radio-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: var(--fs-button);
    user-select: none;
}
.radio-input {
    display: none; 
}
.radio-custom {
    width: 18px;
    height: 18px;
    border: 2px solid var(--palette-stroke);
    border-radius: 50%;
    display: inline-block;
    position: relative;
    transition: all 0.2s ease;
}
.radio-input:checked + .radio-custom {
    border-color: var(--palette-violet);
    background-color: var(--palette-violet);
}
.radio-input:checked + .radio-custom::after {
    content: "";
    width: 8px;
    height: 8px;
    background: var(--palette-white);
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
.first-playlist {
    outline: none;
    display: inline-block;
    height: 40px;
    line-height: 40px;
    border: 1px solid var(--palette-dark);
    text-indent: 16px;
    @include mixins.text-small();
}
</style>
