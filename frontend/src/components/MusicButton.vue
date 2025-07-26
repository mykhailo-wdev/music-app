<template>
    <button :class="['btn', typeBtn]" @click="action">{{ text }}</button>
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

<style lang="scss" scoped>
.btn {
    cursor: pointer;
    display: inline-block;
    height: 40px;
    padding: 0 32px;
    font-size: 18px;
    font-weight: 400;
    line-height: 40px;
    color: #ffffff;
    border-radius: 8px;
    box-shadow: 1px 1px 5px #888888;
    transition: all 0.3s ease-in-out;
}

.btn-hot {
    background-color: #db5566;
    &:hover {
        color: #fff;
        background-color: #df6a78;
    }
    &:active {
        color: #fff;
        background-color: #c04b59;
    }
}

.btn-sunny {
    background-color: #f4ad49;
    &:hover {
        color: #fff;
        background-color: #f5b75f;
    }
    &:active {
        color: #fff;
        background-color: #d69840;
    }
}

.btn-fresh {
    background-color: #51bf87;
    &:hover {
        color: #fff;
        background-color: #66c796;
    }
    &:active {
        color: #fff;
        background-color: #47a877;
    }
}

.btn-sky {
    background-color: #0bacd3;
    &:hover {
        color: #fff;
        background-color: #29b6d8;
    }
    &:active {
        color: #fff;
        background-color: #0a97b9;
    }
}


</style>