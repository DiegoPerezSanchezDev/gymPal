<template>
    <div :class="['stat-card', variant]" @click="handleClick">
        <div class="stat-icon">{{ icon }}</div>
        <div class="stat-content">
            <div class="stat-label">{{ label }}</div>
            <div class="stat-value">{{ formattedValue }}</div>
            <div v-if="subtitle" class="stat-subtitle">{{ subtitle }}</div>
        </div>
        <div v-if="trend" :class="['stat-trend', trend.type]">
            <span class="trend-icon">{{ trend.type === 'up' ? '📈' : '📉' }}</span>
            <span class="trend-value">{{ trend.value }}</span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    icon: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [Number, String],
        required: true,
    },
    subtitle: {
        type: String,
        default: null,
    },
    trend: {
        type: Object,
        default: null,
        // { type: 'up' | 'down', value: '+15%' }
    },
    variant: {
        type: String,
        default: 'default',
        // 'default' | 'primary' | 'success' | 'warning'
    },
    clickable: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['click']);

const formattedValue = computed(() => {
    if (typeof props.value === 'number') {
        // Formatear números grandes con separadores
        return props.value.toLocaleString('es-ES');
    }
    return props.value;
});

function handleClick() {
    if (props.clickable) {
        emit('click');
    }
}
</script>

<style scoped>
.stat-card {
    background: white;
    border-radius: 24px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    border: 1px solid #f1f5f9;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dark .stat-card.default {
    background: rgba(30, 41, 59, 0.5);
    border-color: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(12px);
    box-shadow: none;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #6366f1, #a855f7);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border-color: #6366f1;
}

.dark .stat-card:hover {
    border-color: rgba(99, 102, 241, 0.4);
    background: rgba(30, 41, 59, 0.7);
}

.stat-card:hover .stat-icon {
    animation: float 2s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0); }
    50% { transform: translateY(-5px) rotate(10deg); }
}

.stat-card.clickable {
    cursor: pointer;
}

.stat-card.primary {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
}

.stat-card.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.stat-card.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.stat-icon {
    font-size: 2.5rem;
    line-height: 1;
    flex-shrink: 0;
}

.stat-content {
    flex: 1;
    min-width: 0;
}

.stat-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: color 0.3s ease;
}

.dark .stat-card.default .stat-label {
    color: #9ca3af;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 4px;
    color: #1f2937;
    transition: color 0.3s ease;
}

.dark .stat-card.default .stat-value {
    color: #f9fafb;
}

.stat-subtitle {
    font-size: 0.75rem;
    color: #6b7280;
    transition: color 0.3s ease;
}

.dark .stat-card.default .stat-subtitle {
    color: #9ca3af;
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.stat-trend.up {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.stat-trend.down {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.trend-icon {
    font-size: 1rem;
}

.trend-value {
    font-size: 0.875rem;
}

/* Variant colors for text */
.stat-card.primary .stat-label,
.stat-card.primary .stat-value,
.stat-card.primary .stat-subtitle {
    color: white;
}

.stat-card.success .stat-label,
.stat-card.success .stat-value,
.stat-card.success .stat-subtitle {
    color: white;
}

.stat-card.warning .stat-label,
.stat-card.warning .stat-value,
.stat-card.warning .stat-subtitle {
    color: white;
}

/* Responsive */
@media (max-width: 640px) {
    .stat-card {
        padding: 16px;
    }
    
    .stat-icon {
        font-size: 2rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .stat-trend {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 4px 8px;
        font-size: 0.75rem;
    }
}
</style>
