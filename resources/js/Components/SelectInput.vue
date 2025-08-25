<script setup>
// Este componente está diseñado para ser controlado desde fuera con v-model.
// No necesita emitir eventos manualmente gracias a cómo lo usaremos.
defineProps({
    modelValue: [String, Number], // El valor seleccionado
    label: String,                // El texto de la etiqueta
    id: String,                   // El ID para el 'for' de la etiqueta
    options: {                    // El array de opciones para el dropdown
        type: Array,
        required: true,
        // Valida que cada opción tenga al menos 'value' y 'label'
        validator: (value) => value.every(opt => 'value' in opt && 'label' in opt)
    }
});
</script>

<template>
    <div>
        <!-- La etiqueta se muestra claramente encima del campo -->
        <label :for="id" class="block text-sm font-medium leading-6 text-gray-900">{{ label }}</label>
        
        <!-- Contenedor relativo que nos permite posicionar la flecha dentro -->
        <div class="relative mt-2">
            <select
                :id="id"
                :value="modelValue"
                @change="$emit('update:modelValue', $event.target.value)"
                class="block w-full appearance-none rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
            >
                <!-- Iteramos sobre las opciones que nos pasan como prop -->
                <option v-for="option in options" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>
            
            <!-- Nuestra flecha SVG personalizada y perfectamente alineada -->
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </div>
</template>