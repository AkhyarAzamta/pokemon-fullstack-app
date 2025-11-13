<template>
  <div class="page-container">
    <!-- Back Button -->
    <button @click="goBack" class="back-btn">
      ← Back to List
    </button>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading"></div>
      <p>Loading Pokémon details...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <h3>⚠️ Pokémon Not Found</h3>
      <p>{{ error }}</p>
      <button @click="goToHome" class="btn btn-primary">Back to Home</button>
    </div>

    <!-- Pokémon Detail -->
    <div v-else-if="pokemon" class="pokemon-detail">
      <div class="detail-grid">
        <!-- Left Column - Basic Info -->
        <div class="basic-info card">
          <div class="pokemon-image">
            <img 
              :src="pokemon.sprite" 
              :alt="pokemon.name"
              @error="handleImageError"
            />
          </div>
          
          <h1 class="pokemon-name">{{ capitalize(pokemon.name) }}</h1>
          <p class="pokemon-id">#{{ pokemon.id.toString().padStart(3, '0') }}</p>

          <!-- Types -->
          <div class="types-container">
            <span 
              v-for="type in pokemon.types" 
              :key="type"
              class="type-badge"
              :style="{ backgroundColor: getTypeColor(type) }"
            >
              {{ capitalize(type) }}
            </span>
          </div>

          <!-- Physical Attributes -->
          <div class="attributes">
            <div class="attribute">
              <span class="label">Height</span>
              <span class="value">{{ (pokemon.height / 10).toFixed(1) }} m</span>
            </div>
            <div class="attribute">
              <span class="label">Weight</span>
              <span class="value">{{ (pokemon.weight / 10).toFixed(1) }} kg</span>
            </div>
          </div>

          <!-- Favorite Button -->
          <button 
            @click="toggleFavorite"
            class="favorite-btn large"
            :class="{ 'favorited': pokemon.is_favorite }"
          >
            ⭐ {{ pokemon.is_favorite ? 'Remove from Favorites' : 'Add to Favorites' }}
          </button>
        </div>

        <!-- Right Column - Stats & Abilities -->
        <div class="details-column">
          <!-- Stats -->
          <div class="stats card">
            <h2>Base Stats</h2>
            <div class="stats-grid">
              <div 
                v-for="stat in pokemon.stats" 
                :key="stat.name"
                class="stat-item"
              >
                <div class="stat-header">
                  <span class="stat-name">{{ formatStatName(stat.name) }}</span>
                  <span class="stat-value">{{ stat.value }}</span>
                </div>
                <div class="stat-bar">
                  <div 
                    class="stat-fill"
                    :style="{ width: `${Math.min(100, (stat.value / 255) * 100)}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Abilities -->
          <div class="abilities card">
            <h2>Abilities</h2>
            <div class="abilities-grid">
              <div 
                v-for="ability in pokemon.abilities" 
                :key="ability.name"
                class="ability-item"
              >
                <span class="ability-name">{{ capitalize(ability.name) }}</span>
                <span v-if="ability.is_hidden" class="hidden-badge">Hidden</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { pokemonService } from '@/services/api';
import { capitalize, getTypeColor, formatStatName } from '@/utils/helpers';
import type { PokemonDetail, RawPokemonDetail } from '@/types/pokemon';

const route = useRoute();
const router = useRouter();

const pokemon = ref<PokemonDetail | null>(null);
const loading = ref(false);
const error = ref<string | null>(null);

// Define props and emits
const props = defineProps<{
  favoritesCount: number;
}>();

const emit = defineEmits<{
  'update-favorites': [number];
}>();

// Helper functions for data transformation
const transformType = (typeData: string | { slot?: number; type?: { name: string; url: string }; name?: string }): string => {
  if (typeof typeData === 'string') {
    return typeData;
  }
  return typeData.type?.name || typeData.name || 'unknown';
};

const transformAbility = (abilityData: { ability?: { name: string; url: string }; name?: string; is_hidden?: boolean; slot?: number }): { name: string; is_hidden: boolean } => {
  return {
    name: abilityData.ability?.name || abilityData.name || 'unknown',
    is_hidden: abilityData.is_hidden || false
  };
};

const transformStat = (statData: { base_stat?: number; effort?: number; value?: number; stat?: { name: string; url: string }; name?: string }): { name: string; value: number } => {
  return {
    name: statData.stat?.name || statData.name || 'unknown',
    value: statData.base_stat || statData.value || 0
  };
};

const transformPokemonData = (rawData: RawPokemonDetail): PokemonDetail => {
  return {
    id: rawData.id,
    name: rawData.name,
    height: rawData.height,
    weight: rawData.weight,
    types: Array.isArray(rawData.types) 
      ? rawData.types.map(transformType).filter(type => type !== 'unknown')
      : [],
    abilities: Array.isArray(rawData.abilities)
      ? rawData.abilities.map(transformAbility).filter(ability => ability.name !== 'unknown')
      : [],
    stats: Array.isArray(rawData.stats)
      ? rawData.stats.map(transformStat).filter(stat => stat.name !== 'unknown')
      : [],
    sprite: rawData.sprite || rawData.sprites?.front_default || '',
    is_favorite: rawData.is_favorite || false
  };
};

const loadPokemonDetail = async () => {
  const pokemonId = route.params.id as string;
  
  if (!pokemonId) {
    error.value = 'Invalid Pokémon ID';
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    const response = await pokemonService.getPokemonDetail(pokemonId);
    
    // Transform raw API data to our PokemonDetail interface
    const transformedData = transformPokemonData(response.data as RawPokemonDetail);
    pokemon.value = transformedData;
    
    console.log('Transformed Pokemon data:', pokemon.value);
  } catch (err) {
    error.value = (err as Error).message;
  } finally {
    loading.value = false;
  }
};

const toggleFavorite = async () => {
  if (!pokemon.value) return;

  try {
    const response = await pokemonService.toggleFavorite(pokemon.value.name);
    
    // Update local favorite status immediately
    if (pokemon.value) {
      pokemon.value.is_favorite = response.is_favorite;
    }
    
    // Calculate new count based on current action
    const newCount = response.is_favorite 
      ? props.favoritesCount + 1 
      : props.favoritesCount - 1;
    
    // Emit the new count to parent
    emit('update-favorites', newCount);
    
  } catch (err) {
    console.error('Failed to toggle favorite:', err);
    // Reload to get correct status if there was an error
    await loadPokemonDetail();
  }
};

const goBack = () => {
  router.back();
};

const goToHome = () => {
  router.push('/');
};

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement;
  target.src = 'https://via.placeholder.com/200x200?text=Pokémon';
};

// Watch for route changes
watch(() => route.params.id, loadPokemonDetail);

onMounted(() => {
  loadPokemonDetail();
});
</script>