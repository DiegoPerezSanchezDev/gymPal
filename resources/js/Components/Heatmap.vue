<template>
    <div class="heatmap-container">
        <div class="heatmap-header">
            <h3 class="heatmap-title">🔥 Actividad de Entrenamientos</h3>
            <div class="heatmap-legend">
                <span class="legend-label">Menos</span>
                <div class="legend-boxes">
                    <div class="legend-box level-0" title="Sin entrenamientos"></div>
                    <div class="legend-box level-1" title="1 entrenamiento"></div>
                    <div class="legend-box level-2" title="2 entrenamientos"></div>
                    <div class="legend-box level-3" title="3 entrenamientos"></div>
                    <div class="legend-box level-4" title="4+ entrenamientos"></div>
                </div>
                <span class="legend-label">Más</span>
            </div>
        </div>
        
        <div class="heatmap-grid-wrapper">
            <!-- Day labels (Mon, Wed, Fri) -->
            <div class="day-labels">
                <div class="day-label">Lun</div>
                <div class="day-label"></div>
                <div class="day-label">Mié</div>
                <div class="day-label"></div>
                <div class="day-label">Vie</div>
                <div class="day-label"></div>
                <div class="day-label"></div>
            </div>
            
            <!-- Heatmap grid -->
            <div class="heatmap-grid">
                <div
                    v-for="(day, index) in heatmapData"
                    :key="index"
                    :class="['heatmap-cell', `level-${day.level}`]"
                    :title="getTooltip(day)"
                    :data-date="day.date"
                    @mouseenter="showTooltip($event, day)"
                    @mouseleave="hideTooltip"
                    @click="$emit('day-click', day)"
                ></div>
            </div>
            
            <!-- Month labels - DEBAJO del grid (simple y horizontal) -->
            <div class="month-labels-bottom">
                <span class="month-label">Ene</span>
                <span class="month-label">Feb</span>
                <span class="month-label">Mar</span>
                <span class="month-label">Abr</span>
                <span class="month-label">May</span>
                <span class="month-label">Jun</span>
                <span class="month-label">Jul</span>
                <span class="month-label">Ago</span>
                <span class="month-label">Sep</span>
                <span class="month-label">Oct</span>
                <span class="month-label">Nov</span>
                <span class="month-label">Dic</span>
            </div>
        </div>
        
        <!-- Custom tooltip -->
        <div 
            v-if="tooltipVisible" 
            class="heatmap-tooltip"
            :style="{ left: tooltipX + 'px', top: tooltipY + 'px' }"
        >
            <div class="tooltip-date">{{ tooltipData.formattedDate }}</div>
            <div class="tooltip-count">
                {{ tooltipData.count === 0 ? 'Sin entrenamientos' : 
                   tooltipData.count === 1 ? '1 entrenamiento' : 
                   `${tooltipData.count} entrenamientos` }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['day-click']);

const tooltipVisible = ref(false);
const tooltipX = ref(0);
const tooltipY = ref(0);
const tooltipData = ref({});

// Organizar datos en formato de grid (7 filas x 53 columnas aprox)
const heatmapData = computed(() => {
    return props.data;
});

// Calcular labels de meses
const monthLabels = computed(() => {
    if (!props.data || props.data.length === 0) return [];
    
    const labels = [];
    const monthsData = {};
    
    // Agrupar por mes y calcular columnas
    // IMPORTANTE: El grid usa grid-auto-flow: column, por lo que:
    // - Los primeros 7 elementos están en la columna 1
    // - Los siguientes 7 en la columna 2, etc.
    props.data.forEach((day, index) => {
        const date = new Date(day.date);
        const month = date.getMonth();
        const year = date.getFullYear();
        const monthKey = `${year}-${month}`;
        
        // Calcular columna: como el grid se llena por columnas (7 filas),
        // la columna es index % 7 da la fila, y Math.floor(index / 7) da la columna
        const col = Math.floor(index / 7) + 1;
        
        if (!monthsData[monthKey]) {
            monthsData[monthKey] = {
                month: month,
                minCol: col,
                maxCol: col,
            };
        } else {
            monthsData[monthKey].maxCol = col;
        }
    });
    
    // Convertir a labels
    Object.values(monthsData).forEach(data => {
        const span = data.maxCol - data.minCol + 1;
        if (span > 2) {
            labels.push({
                name: getMonthName(data.month),
                column: data.minCol,
                span: span,
                index: data.month,
            });
        }
    });
    
    return labels;
});

function getMonthName(monthIndex) {
    const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    return months[monthIndex];
}

function getTooltip(day) {
    const date = new Date(day.date);
    const formattedDate = date.toLocaleDateString('es-ES', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    if (day.count === 0) {
        return `${formattedDate}: Sin entrenamientos`;
    } else if (day.count === 1) {
        return `${formattedDate}: 1 entrenamiento`;
    } else {
        return `${formattedDate}: ${day.count} entrenamientos`;
    }
}

function showTooltip(event, day) {
    const date = new Date(day.date);
    tooltipData.value = {
        formattedDate: date.toLocaleDateString('es-ES', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        }),
        count: day.count,
    };
    
    // Calcular posición del tooltip
    let x = event.pageX + 10;
    let y = event.pageY - 40;
    
    // Ajustar si se sale por la derecha
    const tooltipWidth = 250; // Ancho aproximado del tooltip
    if (x + tooltipWidth > window.innerWidth + window.scrollX) {
        x = event.pageX - tooltipWidth - 10;
    }
    
    // Ajustar si se sale por arriba
    if (y < window.scrollY) {
        y = event.pageY + 20;
    }
    
    // Ajustar si se sale por abajo
    const tooltipHeight = 60; // Alto aproximado del tooltip
    if (y + tooltipHeight > window.innerHeight + window.scrollY) {
        y = event.pageY - tooltipHeight - 10;
    }
    
    tooltipX.value = x;
    tooltipY.value = y;
    tooltipVisible.value = true;
}

function hideTooltip() {
    tooltipVisible.value = false;
}
</script>

<style scoped>
.heatmap-container {
    background: white;
    border-radius: 16px;
    padding: 24px;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

.dark .heatmap-container {
    background: #1f2937;
    border-color: #374151;
}

.heatmap-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 16px;
}

.heatmap-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    transition: color 0.3s ease;
}

.dark .heatmap-title {
    color: #f9fafb;
}

.heatmap-legend {
    display: flex;
    align-items: center;
    gap: 8px;
}

.legend-label {
    font-size: 0.75rem;
    color: #6b7280;
    transition: color 0.3s ease;
}

.dark .legend-label {
    color: #9ca3af;
}

.legend-boxes {
    display: flex;
    gap: 4px;
}

.legend-box {
    width: 12px;
    height: 12px;
    border-radius: 2px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    transition: border-color 0.3s ease;
}

.dark .legend-box {
    border-color: rgba(255, 255, 255, 0.2);
}

.heatmap-grid-wrapper {
    position: relative;
    overflow-x: auto;
    padding-top: 0;
}

.month-labels-bottom {
    margin-left: 40px;
    margin-top: 8px;
    display: flex;
    gap: 0;
    justify-content: space-between;
    /* Ancho del grid: 53 columnas * 12px + gaps */
    width: calc(53 * 12px + 52 * 3px);
}

.month-label {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
    transition: color 0.3s ease;
}

.dark .month-label {
    color: #9ca3af;
}

.day-labels {
    position: absolute;
    left: 0;
    top: 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
    width: 35px;
}

.day-label {
    height: 12px;
    font-size: 0.7rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 5px;
    transition: color 0.3s ease;
}

.dark .day-label {
    color: #9ca3af;
}

.heatmap-grid {
    margin-left: 40px;
    display: grid;
    grid-template-rows: repeat(7, 12px);
    grid-auto-flow: column;
    grid-auto-columns: 12px;
    gap: 3px;
}

.heatmap-cell {
    width: 12px;
    height: 12px;
    border-radius: 2px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.dark .heatmap-cell {
    border-color: rgba(255, 255, 255, 0.1);
}

.heatmap-cell:hover {
    transform: scale(1.3);
    border-color: rgba(0, 0, 0, 0.3);
    z-index: 10;
}

.dark .heatmap-cell:hover {
    border-color: rgba(255, 255, 255, 0.5);
}

/* Niveles de actividad - Light mode */
.level-0 {
    background-color: #ebedf0;
}

.level-1 {
    background-color: #c6e0ff;
}

.level-2 {
    background-color: #7cb3ff;
}

.level-3 {
    background-color: #4f8fff;
}

.level-4 {
    background-color: #1a5eff;
}

/* Niveles de actividad - Dark mode */
.dark .level-0 {
    background-color: #161b22;
}

.dark .level-1 {
    background-color: #0e4429;
}

.dark .level-2 {
    background-color: #006d32;
}

.dark .level-3 {
    background-color: #26a641;
}

.dark .level-4 {
    background-color: #39d353;
}

/* Dummy cells (padding) */
.level--1 {
    background-color: transparent;
    border: none;
    cursor: default;
    pointer-events: none;
}

.dark .level--1 {
    background-color: transparent;
    border: none;
}

.heatmap-tooltip {
    position: fixed;
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 0.875rem;
    pointer-events: none;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.tooltip-date {
    font-weight: 600;
    margin-bottom: 4px;
    text-transform: capitalize;
}

.tooltip-count {
    color: #d1d5db;
    font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 768px) {
    .heatmap-container {
        padding: 16px;
    }
    
    .heatmap-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .heatmap-grid-wrapper {
        overflow-x: scroll;
    }
}
</style>
