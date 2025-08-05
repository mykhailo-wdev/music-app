<template>
    <div class="alert-wrap">
        <h3>{{ alertTitle }}</h3>
        <p class="body-text">{{ alertDescription }}</p>
        <div class="alert-btns">
            <music-button
                typeBtn="btn-hot" text="Close"
                @click="closeAlert"
                    
            ></music-button>
            <music-button 
                type-btn="btn-sky" 
                text="Реєстрація" to="/register"
            ></music-button>
            

        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import MusicButton from './MusicButton.vue';
const props = defineProps({
    alertTitle: {
        type: String,
        required: true
    },
    alertDescription: {
        type: String,
        required: true
    }
})

const emits = defineEmits(['close']);
function closeAlert() {
    emits('close')
}




</script>

<style lang="scss">
.alert-wrap {
    position: fixed;
    z-index: 100;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100%;
    max-width: 768px;
    box-shadow: 6px 6px 15px -8px rgba(0,0,0,0.75);
    -webkit-box-shadow: 6px 6px 15px -8px rgba(0,0,0,0.75);
    -moz-box-shadow: 6px 6px 15px -8px rgba(0,0,0,0.75);
    padding: var(--m-space-32) var(--m-space-32);
    background-color: var(--palette-light);
    border-left: 4px solid var(--palette-darkred);
}
.body-text {
    margin-top: var(--m-space-16);
}
.alert-btns {
    margin-top: var(--m-space-16);
    display: grid;
    grid-template-columns: auto auto;
    gap: var(--m-space-16);
}
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.5s ease;
}   

.slide-fade-enter-from {
    opacity: 0;
    transform: translateX(-50%) translateY(200px); 
}

.slide-fade-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(-200px); 
}

</style>