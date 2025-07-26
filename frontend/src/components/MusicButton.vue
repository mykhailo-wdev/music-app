<template>
    <button :class="['btn', typeBtn]" @click="action" type="btn">{{ text }}</button>
</template>

<script setup>
const emit = defineEmits(['action']);
const props = defineProps({
    typeBtn: {
        type: String,
        required: false,
        default: '',
        validator(val) {
            return ['btn-hot', 'btn-sunny',  'btn-fresh', 'btn-sky', ''].includes(val);
        }
    },
    text: {
        type: String,
        required: false,
        default: 'Click'
    }
})

function action() {
  emit('action');
}
</script>

<style lang="scss">
.btn {
    cursor: pointer;
    display: inline-block;
    height: 40px;
    font-family: var(--f-family);
    padding: 0 var(--m-space-32);
    font-size: var(--fs-button);
    font-weight: 400;
    line-height: 40px;
    color: var(--palette-white);
    border-radius: 8px;
    box-shadow: 1px 1px 5px var(--palette-btn-shadow);
    transition: all 0.3s ease-in-out;
}

.btn-hot {
    background-color: var(--palette-hot);
    &:hover {
        color: var(--palette-white);
        background-color: var(--palette-hot--hover);
    }
    &:active {
        color: var(--palette-white);
        background-color: var(--palette-hot--active);
    }
}

.btn-sunny {
    background-color: var(--palette-sunny);
    &:hover {
        color: var(--palette-white);
        background-color: var(--palette-sunny--hover);
    }
    &:active {
        color: var(--palette-white);
        background-color: var(--palette-sunny--active);
    }
}

.btn-fresh {
    background-color: var(--palette-fresh);
    &:hover {
        color: var(--palette-white);
        background-color: var(--palette-fresh--hover);
    }
    &:active {
        color: var(--palette-white);
        background-color: var(--palette-fresh--active);
    }
}

.btn-sky {
    background-color: var(--palette-sky);
    &:hover {
        color: var(--palette-white);
        background-color: var(--palette-sky--hover);
    }
    &:active {
        color: var(--palette-white);
        background-color: var(--palette-sky--active);
    }
}

.btn:disabled {
    opacity: 0.5;
    pointer-events: none;
}

</style>