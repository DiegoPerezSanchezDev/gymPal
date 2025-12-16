<template>
    <AuthenticatedLayout>
        <div class="stats-page">
            <!-- Header con Nivel y XP -->
            <div class="stats-header">
                <div v-if="isViewingOther" class="mb-4">
                    <button 
                        @click="safe_goBack" 
                        class="flex items-center text-white/80 hover:text-white transition-colors text-sm font-medium"
                    >
                        <span class="mr-2">⬅</span> Volver a mis estadísticas
                    </button>
                </div>
                
                <div class="level-container">
                    <div class="level-badge">
                        <div class="level-icon">⭐</div>
                        <div class="level-info">
                            <div class="level-label">{{ isViewingOther ? 'Nivel de ' + user.name : 'Tu Nivel' }}</div>
                            <div class="flex items-baseline gap-3">
                                <div class="level-number">{{ level.level }}</div>
                                <div class="text-sm sm:text-lg font-bold text-yellow-300 uppercase tracking-widest border border-yellow-300/30 px-2 py-0.5 rounded shadow-sm backdrop-blur-sm" v-if="level.rankName">
                                    {{ level.rankName }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="xp-info">
                        <div class="xp-text">
                            <span class="xp-current">{{ level.progressXP }} XP</span>
                            <span class="xp-separator">/</span>
                            <span class="xp-required">{{ level.requiredXP }} XP</span>
                        </div>
                        <div class="xp-bar">
                            <div 
                                class="xp-progress" 
                                :style="{ width: level.progressPercent + '%' }"
                            ></div>
                        </div>
                        <div class="xp-subtitle">
                            {{ level.requiredXP - level.progressXP }} XP para nivel {{ level.level + 1 }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- COMPARACIÓN CARA A CARA (Solo si veo a otro) -->
            <div v-if="isViewingOther && comparison" class="section comparison-section">
                <div class="section-header mb-6">
                    <h2 class="section-title text-center w-full">🆚 Cara a Cara</h2>
                </div>
                
                <div class="comparison-grid">
                    <!-- User Names -->
                    <div class="comparison-names">
                        <span class="my-name">Tú</span>
                        <span class="vs-badge">VS</span>
                        <span class="their-name">{{ user.name }}</span>
                    </div>

                    <!-- Workouts Bar -->
                    <div class="comparison-row">
                        <div class="comp-label">Total Workouts</div>
                        <div class="comp-bars">
                            <span class="comp-val left">{{ comparison.stats.totalWorkouts }}</span>
                            <div class="bar-container">
                                <div class="bar left" :style="{ width: (comparison.stats.totalWorkouts / (comparison.stats.totalWorkouts + stats.totalWorkouts || 1)) * 100 + '%' }"></div>
                                <div class="bar right" :style="{ width: (stats.totalWorkouts / (comparison.stats.totalWorkouts + stats.totalWorkouts || 1)) * 100 + '%' }"></div>
                            </div>
                            <span class="comp-val right">{{ stats.totalWorkouts }}</span>
                        </div>
                    </div>

                    <!-- Volume Bar -->
                    <div class="comparison-row">
                        <div class="comp-label">Volumen (kg)</div>
                        <div class="comp-bars">
                            <span class="comp-val left">{{ safe_formatVolume(comparison.stats.totalVolume) }}</span>
                            <div class="bar-container">
                                <div class="bar left" :style="{ width: (comparison.stats.totalVolume / (comparison.stats.totalVolume + stats.totalVolume || 1)) * 100 + '%' }"></div>
                                <div class="bar right" :style="{ width: (stats.totalVolume / (comparison.stats.totalVolume + stats.totalVolume || 1)) * 100 + '%' }"></div>
                            </div>
                            <span class="comp-val right">{{ safe_formatVolume(stats.totalVolume) }}</span>
                        </div>
                    </div>

                    <!-- Streak Bar -->
                    <div class="comparison-row">
                        <div class="comp-label">Racha (días)</div>
                        <div class="comp-bars">
                            <span class="comp-val left">{{ comparison.streak.current }}</span>
                            <div class="bar-container">
                                <div class="bar left" :style="{ width: (comparison.streak.current / (comparison.streak.current + streak.current || 1)) * 100 + '%' }"></div>
                                <div class="bar right" :style="{ width: (streak.current / (comparison.streak.current + streak.current || 1)) * 100 + '%' }"></div>
                            </div>
                            <span class="comp-val right">{{ streak.current }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insights personalizados -->
            <div v-if="insights.length > 0" class="insights-section">
                <div 
                    v-for="(insight, index) in insights" 
                    :key="index"
                    :class="['insight-card', insight.type]"
                >
                    <span class="insight-icon">{{ insight.icon }}</span>
                    <span class="insight-message">{{ insight.message }}</span>
                </div>
            </div>

            <!-- Ranking Semanal Leaderboard -->
            <div class="section leaderboard-section">
                <div class="section-header mb-4">
                    <h2 class="section-title flex items-center gap-2">
                        <span>🏆</span> Ranking Semanal
                    </h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">GymPals</span>
                </div>
                
                <div class="leaderboard-list">
                    <div 
                        v-for="(entry, index) in leaderboard" 
                        :key="entry.id"
                        :class="['leaderboard-item', { 'is-me': entry.is_me, 'clickable': true }]"
                        @click="safe_visitUserStats(entry.username)"
                    >
                        <div class="rank-position">
                            <span v-if="index === 0" class="medal gold">🥇</span>
                            <span v-else-if="index === 1" class="medal silver">🥈</span>
                            <span v-else-if="index === 2" class="medal bronze">🥉</span>
                            <span v-else class="rank-number">#{{ index + 1 }}</span>
                        </div>
                        
                        <div class="user-info">
                            <div class="user-avatar-placeholder" :style="{ backgroundColor: safe_stringToColor(entry.username) }">
                                {{ entry.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="user-details">
                                <span class="user-name">
                                    {{ entry.name }}
                                    <span v-if="entry.is_me" class="me-badge">(Tú)</span>
                                </span>
                                <span class="user-username">@{{ entry.username }}</span>
                            </div>
                        </div>
                        
                        <div class="workout-count">
                            <span class="count">{{ entry.workouts }}</span>
                            <span class="label">workouts</span>
                        </div>
                    </div>
                    
                    <div v-if="leaderboard.length === 0" class="empty-leaderboard">
                        <p>No hay actividad esta semana todavía.</p>
                        <p class="text-sm mt-1">¡Sé el primero en entrenar!</p>
                    </div>
                </div>
            </div>

            <!-- Heatmap de Actividad -->
            <div class="section">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Mapa de Actividad
                    </h3>
                    <div class="flex items-center bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700 h-9">
                        <button 
                            @click="prevYear" 
                            class="px-3 h-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 transition flex items-center justify-center"
                        >
                            ←
                        </button>
                        <div class="px-4 font-bold text-sm text-gray-700 dark:text-gray-200 min-w-[60px] text-center">
                            {{ selectedYear }}
                        </div>
                        <button 
                            @click="nextYear"
                            :disabled="selectedYear >= currentYear"
                            class="px-3 h-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 border-l border-gray-200 dark:border-gray-700 transition flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed"
                        >
                            →
                        </button>
                    </div>
                </div>
                <Heatmap :data="heatmap" @day-click="handleDayClick" />
            </div>

            <!-- Stats Cards Grid -->
            <div class="stats-grid">
                <StatCard
                    icon="💪"
                    label="Récords Personales"
                    :value="personalRecords.length"
                    subtitle="Ejercicios con PR"
                    variant="primary"
                />
                
                <StatCard
                    icon="🔥"
                    label="Racha Actual"
                    :value="streak.current"
                    :subtitle="`Récord: ${streak.longest} días`"
                    variant="warning"
                />
                
                <StatCard
                    icon="🏋️"
                    label="Total Workouts"
                    :value="stats.totalWorkouts"
                    :subtitle="`${stats.avgWeekly} por semana`"
                />
                
                <StatCard
                    icon="📈"
                    label="Volumen Total"
                    :value="safe_formatVolume(stats.totalVolume)"
                    subtitle="Kilogramos levantados"
                />
                
                <StatCard
                    icon="⏱️"
                    label="Tiempo Total"
                    :value="stats.totalHours + 'h'"
                    :subtitle="`${stats.totalMinutes} minutos`"
                />
                
                <StatCard
                    icon="📅"
                    label="Este Mes"
                    :value="stats.workoutsThisMonth"
                    :subtitle="`${stats.workoutsThisWeek} esta semana`"
                    variant="success"
                />
            </div>

            <!-- Goals Section -->
            <div class="section" v-if="!isViewingOther">
                <div class="section-header">
                    <h2 class="section-title">🎯 Mis Objetivos</h2>
                    <button @click="openGoalModal" class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium shadow-sm">
                        + Nuevo Objetivo
                    </button>
                </div>

                <div v-if="goals && goals.length > 0" class="goals-grid mb-8">
                    <div v-for="goal in goals" :key="goal.id" class="goal-card group">
                        <div class="flex justify-between items-start mb-3">
                            <div class="goal-icon p-2 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                <span v-if="goal.type === 'weekly_workouts'" class="text-xl">📅</span>
                                <span v-else-if="goal.type === 'weekly_minutes'" class="text-xl">⏱️</span>
                                <span v-else-if="goal.type === 'early_bird'" class="text-xl">🌅</span>
                                <span v-else class="text-xl">🔥</span>
                            </div>
                            <button 
                                @click="deleteGoal(goal.id)" 
                                class="text-gray-400 hover:text-red-500 transition opacity-0 group-hover:opacity-100"
                                title="Eliminar objetivo"
                            >
                                ✕
                            </button>
                        </div>
                        
                        <h3 class="font-bold text-gray-800 dark:text-gray-200 text-lg leading-tight mb-4">
                            <span v-if="goal.type === 'weekly_workouts'">{{ goal.target_value }} Entrenos/Semana</span>
                            <span v-else-if="goal.type === 'weekly_minutes'">{{ goal.target_value }} Minutos/Semana</span>
                            <span v-else-if="goal.type === 'early_bird'">{{ goal.target_value }} Mañanas/Semana</span>
                            <span v-else>Racha de {{ goal.target_value }} días</span>
                        </h3>

                        <div class="mt-auto">
                            <div class="flex justify-between text-xs text-gray-500 mb-1 font-medium">
                                <span v-if="goal.type === 'weekly_minutes'">{{ goal.current_value }} / {{ goal.target_value }} min</span>
                                <span v-else>{{ goal.current_value }} / {{ goal.target_value }}</span>
                                <span>{{ Math.min(Math.round((goal.current_value / goal.target_value) * 100), 100) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 overflow-hidden">
                                <div 
                                    class="h-full rounded-full transition-all duration-1000 ease-out" 
                                    :class="goal.completed ? 'bg-green-500' : 'bg-blue-600'"
                                    :style="{ width: Math.min((goal.current_value / goal.target_value) * 100, 100) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="empty-state text-center py-8 bg-gray-50 dark:bg-gray-800/30 rounded-xl mb-8 border border-dashed border-gray-200 dark:border-gray-700">
                    <p class="text-gray-500 mb-4">No tienes objetivos activos.</p>
                    <button @click="openGoalModal" class="text-blue-600 font-medium hover:underline">¡Crea uno para motivarte!</button>
                </div>
            </div>

            <!-- Badges Section (Vitrina de Trofeos) -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">🏆 {{ isViewingOther ? 'Vitrina de Trofeos' : 'Mis Logros' }}</h2>
                    <span class="badge-count">{{ badges.filter(b => b.unlocked).length }} conseguidos</span>
                </div>
                
                <!-- Badges Desbloqueados -->
                <div v-if="badges.filter(b => b.unlocked).length > 0" class="badges-grid mb-8">
                    <div 
                        v-for="badge in badges.filter(b => b.unlocked)" 
                        :key="badge.id"
                        class="badge-flip-container unlocked"
                        :class="{ 'is-flipped': flippedBadges.has(badge.id) }"
                        @click="toggleBadge(badge.id)"
                    >
                        <div class="badge-flip-inner">
                            <div class="badge-flip-front">
                                <div class="badge-icon">{{ badge.icon }}</div>
                                <div class="badge-name">{{ badge.name }}</div>
                            </div>
                            <div class="badge-flip-back">
                                <span class="text-xs font-bold text-yellow-600 dark:text-yellow-400 mb-1">¡DESBLOQUEADO!</span>
                                <div class="badge-description">{{ badge.description }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="empty-state mb-8 text-center text-gray-500 py-4">
                    {{ isViewingOther ? 'Este usuario aún no ha conseguido ningún logro.' : '¡Entrena para conseguir tu primer logro!' }}
                </div>

                <!-- Badges Bloqueados (Solo visibles para mí) -->
                <div v-if="!isViewingOther && badges.filter(b => !b.unlocked).length > 0">
                    <h3 class="text-sm uppercase tracking-wider text-gray-500 font-bold mb-4">
                        🔒 Próximos Objetivos
                    </h3>
                    <div class="badges-grid opacity-75">
                        <div 
                            v-for="badge in badges.filter(b => !b.unlocked)" 
                            :key="badge.id"
                            class="badge-flip-container locked"
                            :class="{ 'is-flipped': flippedBadges.has(badge.id) }"
                            @click="toggleBadge(badge.id)"
                        >
                            <div class="badge-flip-inner">
                                <div class="badge-flip-front">
                                    <div class="badge-icon grayscale opacity-50">{{ badge.icon }}</div>
                                    <div class="badge-name text-gray-500">{{ badge.name }}</div>
                                    <div class="w-16 h-1 mt-2 bg-gray-200 rounded-full overflow-hidden mx-auto">
                                        <div class="h-full bg-gray-400" :style="{ width: Math.min((badge.progress / badge.target) * 100, 100) + '%' }"></div>
                                    </div>
                                </div>
                                <div class="badge-flip-back">
                                    <span class="text-xs font-bold text-gray-500 mb-1">EN PROGRESO</span>
                                    <div class="badge-description text-gray-600 dark:text-gray-400">{{ badge.description }}</div>
                                    <div class="mt-2 text-xs font-mono font-bold">
                                        {{ badge.progress }} / {{ badge.target }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Records Section -->
            <div v-if="personalRecords.length > 0" class="section">
                <div class="section-header">
                    <h2 class="section-title">💪 Récords Personales</h2>
                </div>
                
                <div class="records-list">
                    <div 
                        v-for="(record, index) in personalRecords" 
                        :key="index"
                        class="record-item"
                    >
                        <div class="record-info">
                            <div class="record-header">
                                <span class="record-rank">#{{ index + 1 }}</span>
                                <span class="record-exercise">{{ record.exercise }}</span>
                            </div>
                            <div class="record-stats">
                                <span class="record-stat">
                                    <strong>{{ record.maxWeight }} kg</strong> × <strong>{{ record.maxWeightReps }} reps</strong>
                                </span>
                                <span class="record-separator">•</span>
                                <span class="record-stat">
                                    {{ record.frequency }} veces
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Charts Section -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">📊 Progreso en el Tiempo</h2>
                </div>
                
                <div class="charts-grid">
                    <!-- Volumen Semanal Chart -->
                    <div class="chart-card">
                        <h3 class="chart-title">Volumen Semanal (kg)</h3>
                        <canvas ref="weeklyVolumeChart"></canvas>
                    </div>
                    
                    <!-- Frecuencia Mensual Chart -->
                    <div class="chart-card">
                        <h3 class="chart-title">Frecuencia Mensual</h3>
                        <canvas ref="monthlyFrequencyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <Modal :show="showGoalModal" @close="closeGoalModal">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">Nuevo Objetivo</h2>
                
                <div class="mb-6">
                    <InputLabel value="Tipo de Objetivo" class="mb-2" />
                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            v-for="type in goalTypes" 
                            :key="type.id"
                            type="button"
                            @click="goalForm.type = type.id"
                            class="flex flex-col items-center justify-center p-3 border rounded-xl transition-all"
                            :class="goalForm.type === type.id 
                                ? 'border-blue-600 bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 dark:border-blue-400 ring-1 ring-blue-600' 
                                : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400'"
                        >
                            <span class="text-2xl mb-1">{{ type.icon }}</span>
                            <span class="font-bold text-sm">{{ type.label }}</span>
                            <span class="text-xs opacity-75 text-center leading-tight mt-1">{{ type.hint }}</span>
                        </button>
                    </div>
                </div>

                <div class="mb-6">
                    <InputLabel for="target" value="Meta a alcanzar" />
                    <div class="flex items-center gap-3 mt-1">
                        <TextInput 
                            id="target" 
                            type="number" 
                            v-model="goalForm.target_value"
                            class="block w-full text-lg font-bold"
                            min="1"
                            placeholder="Ej: 3"
                        />
                        <span class="text-gray-500 font-medium whitespace-nowrap" v-if="goalForm.type === 'weekly_minutes'">minutos</span>
                        <span class="text-gray-500 font-medium whitespace-nowrap" v-else-if="goalForm.type === 'weekly_workouts'">días</span>
                        <span class="text-gray-500 font-medium whitespace-nowrap" v-else-if="goalForm.type === 'early_bird'">sesiones</span>
                        <span class="text-gray-500 font-medium whitespace-nowrap" v-else>días seguidos</span>
                    </div>

                    <p class="text-xs text-gray-500 mt-2 bg-gray-50 dark:bg-gray-800/50 p-2 rounded border border-gray-100 dark:border-gray-700">
                        <span v-if="goalForm.type === 'weekly_workouts'">Ejemplo: 3 entrenamientos (Lunes a Domingo).</span>
                        <span v-if="goalForm.type === 'weekly_minutes'">Ejemplo: 150 minutos de actividad total esta semana.</span>
                        <span v-if="goalForm.type === 'streak'">Ejemplo: Mantener la racha durante 7 días consecutivos.</span>
                        <span v-if="goalForm.type === 'early_bird'">Ejemplo: Entrenar 3 veces antes de las 9:00 AM esta semana.</span>
                    </p>
                    <p v-if="goalForm.errors.target_value" class="text-red-500 text-xs mt-1">{{ goalForm.errors.target_value }}</p>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <SecondaryButton @click="closeGoalModal">Cancelar</SecondaryButton>
                    <PrimaryButton @click="submitGoal" :class="{ 'opacity-25': goalForm.processing }" :disabled="goalForm.processing">
                        Crear Objetivo
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Share Victory Modal -->
        <Modal :show="showAchievementModal" @close="closeAchievementModal">
            <div class="p-8 text-center bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 rounded-lg">
                <div class="mb-4 animate-bounce text-6xl">
                    🏆
                </div>
                <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 to-purple-600 mb-2">
                    ¡Nuevo Logro!
                </h2>
                <div v-if="newBadges.length > 0" class="my-6">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-indigo-100 dark:border-indigo-900 inline-block transform hover:scale-105 transition-transform duration-300">
                        <div class="text-6xl mb-3">{{ newBadges[0].icon }}</div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white">{{ newBadges[0].name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ newBadges[0].description }}</p>
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-8">
                    ¡Has desbloqueado una nueva insignia! Comparte tu victoria con tus amigos.
                </p>
                <div class="flex justify-center gap-4">
                    <SecondaryButton @click="closeAchievementModal">
                        Cerrar
                    </SecondaryButton>
                    <PrimaryButton 
                        @click="shareBadge(newBadges[0])"
                        :class="{ 'opacity-25 cursor-not-allowed': isSharing }"
                        :disabled="isSharing"
                        class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 border-0 shadow-lg"
                    >
                        {{ isSharing ? 'Compartiendo...' : 'Compartir Victoria 🚀' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Day Details Modal -->
        <Modal :show="dayDetailsModalOpen" @close="closeDayDetailsModal">
            <div class="p-6">
                 <div v-if="loadingDayDetails" class="flex justify-center py-8">
                     <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                 </div>
                 <div v-else-if="dayDetails">
                     <h2 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                         📅 {{ dayDetails.date }} 
                         <span class="text-sm font-normal text-gray-500">({{ dayDetails.logs.length }} sesiones)</span>
                     </h2>
                     
                     <div class="space-y-4 max-h-[60vh] overflow-y-auto">
                         <div v-for="log in dayDetails.logs" :key="log.id" class="border dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
                             <div class="flex justify-between items-start mb-2">
                                 <div>
                                     <h3 class="font-bold text-lg text-indigo-600 dark:text-indigo-400">{{ log.workout_name }}</h3>
                                     <div class="text-xs text-gray-500">⏱ {{ log.time }} • ⌛ {{ log.duration }} min</div>
                                 </div>
                                 <div class="text-right">
                                     <div class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ log.volume }} kg</div>
                                     <div class="text-xs text-gray-500">Volumen Total</div>
                                 </div>
                             </div>
                             
                             <div class="mt-3">
                                 <h4 class="text-xs uppercase tracking-wider text-gray-500 mb-2 font-bold">Ejercicios</h4>
                                 <div class="grid grid-cols-1 gap-2">
                                     <div v-for="(ex, idx) in log.exercises" :key="idx" class="flex justify-between text-sm border-b border-gray-100 dark:border-gray-700 pb-1 last:border-0 text-gray-700 dark:text-gray-300">
                                         <span>{{ ex.name }}</span>
                                         <span class="font-mono text-gray-500">{{ ex.sets }} series</span>
                                     </div>
                                 </div>
                             </div>
                         </div>
                         
                         <div v-if="dayDetails.logs.length === 0" class="text-center py-8 text-gray-500">
                             No se encontraron detalles para este día.
                         </div>
                     </div>
                 </div>
                 <div v-else class="text-center py-8 text-red-500">
                     Error al cargar detalles.
                 </div>
                 
                 <div class="mt-6 flex justify-end">
                     <SecondaryButton @click="closeDayDetailsModal">Cerrar</SecondaryButton>
                 </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Heatmap from '@/Components/Heatmap.vue';
import StatCard from '@/Components/StatCard.vue';
import Chart from 'chart.js/auto';
import Modal from '@/Components/Modal.vue'; // Asumo que tienes un componente Modal genérico
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue'; // Asumo que existe o uso select HTML normal

const props = defineProps({
    stats: Object,
    level: Object,
    heatmap: Array,
    leaderboard: Array,
    streak: Object,
    personalRecords: Array,
    badges: Array,
    progressCharts: Object,
    insights: Array,
    user: Object,
    isViewingOther: Boolean,
    filters: Object,
    comparison: Object,
    goals: Array // <--- Objetivos
});

const goalTypes = [
    { id: 'weekly_workouts', label: 'Días por Semana', icon: '📅', hint: 'Número de días' },
    { id: 'weekly_minutes', label: 'Minutos Activos', icon: '⏱️', hint: 'Tiempo total semanal' },
    { id: 'streak', label: 'Racha', icon: '🔥', hint: 'Días seguidos' },
    { id: 'early_bird', label: 'Club 7 AM', icon: '🌅', hint: 'Madrugar (Antes de 9:00)' },
];

// Control de giro de medallas (Click to Flip)
const flippedBadges = ref(new Set());
const toggleBadge = (id) => {
    if (flippedBadges.value.has(id)) {
        flippedBadges.value.delete(id);
    } else {
        flippedBadges.value.add(id);
    }
};

// Estado del Modal
const showGoalModal = ref(false);
const goalForm = useForm({
    type: 'weekly_workouts',
    target_value: '3',
    expiry_date: '',
});

const openGoalModal = () => {
    showGoalModal.value = true;
};

const closeGoalModal = () => {
    showGoalModal.value = false;
    goalForm.reset();
};

const submitGoal = () => {
    goalForm.post(route('goals.store'), {
        onSuccess: () => closeGoalModal(),
    });
};

const deleteGoal = (goalId) => {
    if (confirm('¿Estás seguro de eliminar este objetivo?')) {
        router.delete(route('goals.destroy', goalId));
    }
};

const weeklyVolumeChart = ref(null);
const monthlyFrequencyChart = ref(null);

// Selector de Año
const selectedYear = ref(props.filters?.year || new Date().getFullYear());

const changeYear = (year) => {
    if (year === selectedYear.value) return;
    selectedYear.value = year;
    
    router.get(
        route(props.isViewingOther ? 'stats.show' : 'stats.index', props.isViewingOther ? props.user.username : undefined),
        { year: selectedYear.value },
        { 
            preserveState: true,
            preserveScroll: true,
            only: ['heatmap', 'progressCharts', 'filters'] 
        }
    );
};

const prevYear = () => changeYear(selectedYear.value - 1);
const nextYear = () => changeYear(selectedYear.value + 1);
const currentYear = new Date().getFullYear();

// Funciones auxiliares (Renombradas para evitar colisiones)
const safe_stringToColor = (str) => {
    let hash = 0;
    if (!str) return '#ccc';
    for (let i = 0; i < str.length; i++) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash);
    }
    const c = (hash & 0x00FFFFFF).toString(16).toUpperCase();
    return '#' + '00000'.substring(0, 6 - c.length) + c;
};

const safe_formatVolume = (val) => {
    if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
    if (val >= 1000) return (val / 1000).toFixed(1) + 'k';
    return val;
};

const safe_formatBadgeProgress = (val) => {
    if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
    if (val >= 1000) return (val / 1000).toFixed(0) + 'k';
    return val;
};

const safe_visitUserStats = (username) => {
    router.visit(route('stats.show', username));
};

const safe_goBack = () => {
    router.visit(route('stats.index'));
};

// Lógica de Logros Desbloqueados (Share Victory)
const page = usePage();
const showAchievementModal = ref(false);
const newBadges = ref([]);
const isSharing = ref(false);

watch(() => page.props.flash.new_badges, (val) => {
    if (val && val.length > 0) {
        newBadges.value = val;
        // Pequeño delay para asegurar que el componente Modal se monte y detecte el cambio de prop
        setTimeout(() => {
            showAchievementModal.value = true;
        }, 200);
    }
}, { immediate: true });

const closeAchievementModal = () => {
    showAchievementModal.value = false;
};

const shareBadge = (badge) => {
    isSharing.value = true;
    router.post(route('stats.share'), { badge_id: badge.id }, {
        preserveScroll: true,
        onSuccess: () => {
            showAchievementModal.value = false;
        },
        onFinish: () => {
            isSharing.value = false;
        }
    });
};

// Heatmap Day Details Interactive
const dayDetailsModalOpen = ref(false);
const dayDetails = ref(null);
const loadingDayDetails = ref(false);

const handleDayClick = async (day) => {
    if (!day.date || day.count === 0) return; 
    
    dayDetailsModalOpen.value = true;
    dayDetails.value = null;
    loadingDayDetails.value = true;

    try {
        const response = await axios.get(route('stats.day_details'), {
            params: {
                date: day.date,
                user_id: props.user.id
            }
        });
        dayDetails.value = response.data;
    } catch (error) {
        console.error("Error loading day details:", error);
    } finally {
        loadingDayDetails.value = false;
    }
};

const closeDayDetailsModal = () => {
    dayDetailsModalOpen.value = false;
};

onMounted(() => {
    // 🔌 WebSockets & Real-time Notifications (Configurado y listo para activar)
    // Esta sección escuchará eventos del servidor cuando activemos Laravel Reverb/Pusher
    if (typeof window.Echo !== 'undefined') {
        /*
        window.Echo.private(`user.${props.user.id}`)
            .listen('GoalAchieved', (e) => {
                // TODO: Reemplazar alert con Toast Notification bonita
                alert(`🎉 ¡Objetivo Cumplido! ${e.message}`);
                router.reload({ only: ['goals', 'badges', 'level'] }); 
            })
            .listen('WorkoutStarted', (e) => {
                // TODO: Mostrar indicador de "Entrenando Ahora" en la lista de amigos
                console.log('🔴 Live Workout:', e.friend_name);
            });
        */
        console.log('📡 GymPal Realtime: Ready to connect.');
    }

    // Detectar modo oscuro
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#f9fafb' : '#1f2937';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';
    
    // Crear gráfica de volumen semanal
    if (weeklyVolumeChart.value) {
        new Chart(weeklyVolumeChart.value, {
            type: 'line',
            data: {
                labels: props.progressCharts.weeklyVolume.map(w => w.week),
                datasets: [{
                    label: 'Volumen (kg)',
                    data: props.progressCharts.weeklyVolume.map(w => w.volume),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                        },
                        grid: {
                            color: gridColor,
                        },
                    },
                    x: {
                        ticks: {
                            color: textColor,
                        },
                        grid: {
                            display: false,
                        },
                    },
                },
            },
        });
    }
    
    // Crear gráfica de frecuencia mensual
    if (monthlyFrequencyChart.value) {
        new Chart(monthlyFrequencyChart.value, {
            type: 'bar',
            data: {
                labels: props.progressCharts.monthlyFrequency.map(m => m.month),
                datasets: [{
                    label: 'Workouts',
                    data: props.progressCharts.monthlyFrequency.map(m => m.count),
                    backgroundColor: 'rgba(168, 85, 247, 0.8)',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: textColor,
                        },
                        grid: {
                            color: gridColor,
                        },
                    },
                    x: {
                        ticks: {
                            color: textColor,
                        },
                        grid: {
                            display: false,
                        },
                    },
                },
            },
        });
    }
});
</script>

<style scoped>
.stats-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px;
}

/* Header con Nivel */
.stats-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-radius: 20px;
    padding: 32px;
    margin-bottom: 24px;
    color: white;
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
}

.level-container {
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}

.level-badge {
    display: flex;
    align-items: center;
    gap: 16px;
    background: rgba(255, 255, 255, 0.2);
    padding: 16px 24px;
    border-radius: 16px;
    backdrop-filter: blur(10px);
}

.level-icon {
    font-size: 3rem;
    line-height: 1;
}

.level-info {
    display: flex;
    flex-direction: column;
}

.level-label {
    font-size: 0.875rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.level-number {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
}

.xp-info {
    flex: 1;
    min-width: 300px;
}

.xp-text {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 8px;
}

.xp-current {
    font-size: 1.5rem;
    font-weight: 700;
}

.xp-separator {
    opacity: 0.7;
}

.xp-required {
    font-size: 1.125rem;
    opacity: 0.9;
}

.xp-bar {
    height: 12px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 8px;
}

/* Comparison Section Styles */
.comparison-names {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 24px;
}

.my-name { color: #6366f1; }
.their-name { color: #f97316; }

.vs-badge {
    background: #ef4444;
    color: white;
    font-size: 0.8rem;
    padding: 4px 8px;
    border-radius: 8px;
    font-weight: 900;
    transform: skew(-10deg);
}

.comparison-row {
    margin-bottom: 20px;
}

.comp-label {
    text-align: center;
    font-size: 0.9rem;
    color: #6b7280;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.dark .comp-label { color: #9ca3af; }

.comp-bars {
    display: flex;
    align-items: center;
    gap: 12px;
}

.comp-val {
    width: 60px;
    font-weight: 700;
    font-size: 1.1rem;
}

.comp-val.left { text-align: right; color: #6366f1; }
.comp-val.right { text-align: left; color: #f97316; }

.bar-container {
    flex: 1;
    height: 12px;
    background: rgba(0,0,0,0.05);
    border-radius: 6px;
    overflow: hidden;
    display: flex;
}

.dark .bar-container { background: rgba(255,255,255,0.05); }

.bar.left {
    background: #6366f1;
    height: 100%;
    border-right: 2px solid white;
}

.bar.right {
    background: #f97316;
    height: 100%;
}

.clickable {
    cursor: pointer;
}

/* Leaderboard Styles */
.leaderboard-section {
    position: relative;
    overflow: hidden;
}

.leaderboard-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.leaderboard-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.5);
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 12px;
    transition: all 0.2s ease;
}

.dark .leaderboard-item {
    background: rgba(30, 41, 59, 0.5);
    border-color: rgba(255, 255, 255, 0.05);
}

.leaderboard-item:hover {
    transform: translateX(4px);
    background: rgba(255, 255, 255, 0.8);
}

.dark .leaderboard-item:hover {
    background: rgba(30, 41, 59, 0.8);
}

.leaderboard-item.is-me {
    background: linear-gradient(90deg, rgba(99, 102, 241, 0.1) 0%, transparent 100%);
    border-left: 4px solid #6366f1;
}

.rank-position {
    width: 32px;
    font-size: 1.25rem;
    font-weight: 700;
    text-align: center;
    margin-right: 12px;
}

.medal {
    font-size: 1.5rem;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

.rank-number {
    color: #64748b;
    font-size: 1rem;
}

.dark .rank-number {
    color: #94a3b8;
}

.user-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.95rem;
}

.dark .user-name {
    color: #f3f4f6;
}

.me-badge {
    font-size: 0.75rem;
    color: #6366f1;
    background: rgba(99, 102, 241, 0.1);
    padding: 2px 6px;
    border-radius: 4px;
    margin-left: 4px;
}

.user-username {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: lowercase;
}

.dark .user-username {
    color: #9ca3af;
}

.workout-count {
    text-align: right;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.workout-count .count {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1;
}

.dark .workout-count .count {
    color: #f3f4f6;
}

.workout-count .label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
}

.empty-leaderboard {
    text-align: center;
    padding: 32px;
    background: rgba(0,0,0,0.02);
    border-radius: 12px;
    color: #6b7280;
}

.dark .empty-leaderboard {
    background: rgba(255,255,255,0.02);
    color: #9ca3af;
}

.xp-progress {
    height: 100%;
    background: linear-gradient(90deg, #fbbf24, #f59e0b);
    border-radius: 6px;
    transition: width 0.5s ease;
}

.xp-subtitle {
    font-size: 0.875rem;
    opacity: 0.8;
}

/* Insights */
.insights-section {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.insight-card {
    flex: 1;
    min-width: 250px;
    padding: 16px 20px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    animation: slideIn 0.5s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.insight-card.positive {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
}

.insight-card.streak {
    background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
    color: #92400e;
}

.insight-card.goal {
    background: linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100%);
    color: #5b21b6;
}

.insight-card.warning {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
}

.insight-card.coach {
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #3730a3;
    border-left: 4px solid #4f46e5;
}

.insight-card.info {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    color: #075985;
}

.insight-icon {
    font-size: 1.5rem;
}

.insight-message {
    font-size: 0.9375rem;
}

/* Sections */
.section {
    margin-bottom: 32px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.dark .section-title {
    color: #f9fafb;
}

.badge-count {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 600;
}

.dark .badge-count {
    color: #9ca3af;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

/* Badges Grid */
.badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 16px;
}

.badge-card {
    background: rgba(255, 255, 255, 0.5);
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    border: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: transform 0.2s;
}

.dark .badge-card {
    background: rgba(30, 41, 59, 0.5);
    border-color: rgba(255, 255, 255, 0.05);
}

.badge-card.unlocked {
    background: linear-gradient(135deg, rgba(255,255,255,0.8), rgba(255,255,255,0.4));
    border: 1px solid rgba(255, 215, 0, 0.3); /* Gold hint */
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.dark .badge-card.unlocked {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(30, 41, 59, 0.6));
    border-color: rgba(255, 215, 0, 0.2);
}

.badge-card.locked {
    background: transparent;
    border: 1px dashed rgba(156, 163, 175, 0.5);
}

.badge-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.dark .badge-card.locked {
    background: rgba(17, 24, 39, 0.3);
    border-color: rgba(75, 85, 99, 0.5);
}

.badge-icon {
    font-size: 2.5rem;
    margin-bottom: 8px;
}

.badge-card.locked .badge-icon {
    filter: grayscale(100%);
    opacity: 0.5;
}

.badge-name {
    font-weight: 700;
    font-size: 0.9rem;
    color: #1f2937;
    margin-bottom: 4px;
}

.dark .badge-name {
    color: #f3f4f6;
}

.badge-description {
    font-size: 0.75rem;
    color: #6b7280;
    line-height: 1.2;
}

.dark .badge-description {
    color: #9ca3af;
}

/* Records List */
.records-list {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.dark .records-list {
    background: #1f2937;
    border-color: #374151;
}

.record-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px 24px;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s ease;
}

.record-item:last-child {
    border-bottom: none;
}

.record-item:hover {
    background: #f9fafb;
}

.dark .record-item:hover {
    background: #374151;
}

.record-info {
    flex: 1;
}

.record-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 6px;
}

.record-rank {
    font-size: 1rem;
    font-weight: 700;
    color: #6366f1;
    min-width: auto;
}

.record-exercise {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
}

.dark .record-exercise {
    color: #f9fafb;
}

.record-stats {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.875rem;
    color: #6b7280;
    flex-wrap: wrap;
}

.dark .record-stats {
    color: #9ca3af;
}

.record-separator {
    opacity: 0.5;
}

/* Charts */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 24px;
}

.chart-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    border: 1px solid #e5e7eb;
}

.dark .chart-card {
    background: #1f2937;
    border-color: #374151;
}

.chart-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 20px 0;
}

.dark .chart-title {
    color: #f9fafb;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-page {
        padding: 16px;
    }
    
    .stats-header {
        padding: 24px;
    }
    
    .level-container {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .xp-info {
        width: 100%;
        min-width: 0;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .badges-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }
    
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .record-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
}

/* Flip Card Styles */
.badge-flip-container {
    perspective: 1000px;
    height: 180px;
}

.badge-flip-inner {
    position: relative;
    width: 100%;
    height: 100%;
    text-align: center;
    transition: transform 0.6s;
    transform-style: preserve-3d;
    cursor: pointer;
}

.badge-flip-container.is-flipped .badge-flip-inner {
    transform: rotateY(180deg);
}

.badge-flip-front, .badge-flip-back {
    position: absolute;
    width: 100%;
    height: 100%;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.badge-flip-front {
    background: white;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.badge-flip-container.unlocked .badge-flip-front {
    background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.8));
    border: 1px solid rgba(255, 215, 0, 0.4);
    box-shadow: 0 4px 12px rgba(255, 215, 0, 0.15);
}

.badge-flip-container.locked .badge-flip-front {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border: 1px solid #d1d5db;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.badge-flip-back {
    background: white;
    border: 1px solid rgba(0, 0, 0, 0.1);
    transform: rotateY(180deg);
}

.dark .badge-flip-front,
.dark .badge-flip-back {
    background: #1e293b;
    border-color: rgba(255, 255, 255, 0.05);
}

.dark .badge-flip-container.unlocked .badge-flip-front {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(30, 41, 59, 0.7));
    border-color: rgba(255, 215, 0, 0.3);
    box-shadow: 0 4px 12px rgba(255, 215, 0, 0.1);
}

.dark .badge-flip-container.locked .badge-flip-front {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-color: #374151;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
}

.badge-icon {
    font-size: 2.5rem;
    margin-bottom: 8px;
}
.badge-name {
    font-weight: 700;
    font-size: 0.9rem;
    color: #1f2937;
    margin-bottom: 4px;
}
.dark .badge-name {
    color: #f3f4f6;
}
.badge-description {
    font-size: 0.8rem;
    color: #6b7280;
    line-height: 1.3;
}
.dark .badge-description {
    color: #9ca3af;
}
</style>
