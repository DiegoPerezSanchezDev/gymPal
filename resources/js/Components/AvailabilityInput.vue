<script setup>
const props = defineProps({
    modelValue: { type: Array, default: () => [] },
});
const emit = defineEmits(['update:modelValue']);
const availabilityOptions = ['Mañanas', 'Tardes', 'Noches', 'Fines de semana'];

function toggleAvailability(option) {
    const newSelection = [...props.modelValue];
    const index = newSelection.indexOf(option);
    if (index === -1) { newSelection.push(option); } else { newSelection.splice(index, 1); }
    emit('update:modelValue', newSelection);
}
</script>
<template>
    <div class="flex flex-wrap gap-2">
        <button
            v-for="option in availabilityOptions" :key="option" type="button" @click="toggleAvailability(option)"
            :class="[
                'px-3 py-1.5 text-sm font-medium rounded-full border-2 transition',
                modelValue.includes(option)
                    ? 'bg-pink-600 text-white border-pink-700'
                    : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100'
            ]">
            {{ option }}
        </button>
    </div>
</template>