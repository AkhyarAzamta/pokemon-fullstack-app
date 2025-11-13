<template>
  <div class="page-container">
    <div class="page-header">
      <h1>Pokémon Explorer</h1>
      <p>Discover and manage your favorite Pokémon</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading"></div>
      <p>Loading Pokémon...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <h3>⚠️ Oops!</h3>
      <p>{{ error }}</p>
      <button @click="loadPokemons" class="btn btn-primary">Try Again</button>
    </div>

    <!-- Content -->
    <div v-else>
      <!-- Pagination Controls -->
      <div class="pagination-controls">
        <div class="pagination-info">
          <span>Show:</span>
          <select v-model="limit" @change="handleLimitChange" class="select">
            <option value="20">20</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
          <span>per page</span>
        </div>
        
        <div class="pagination-buttons">
          <button 
            @click="changePage(currentPage - 1)" 
            :disabled="currentPage === 1"
            class="btn btn-primary"
          >
            Previous
          </button>
          <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
          <button 
            @click="changePage(currentPage + 1)" 
            :disabled="currentPage === totalPages"
            class="btn btn-primary"
          >
            Next
          </button>
        </div>
      </div>

      <!-- Pokémon Grid -->
      <div class="grid grid-cols-4">
        <div 
          v-for="pokemon in pokemons" 
          :key="pokemon.name"
          class="pokemon-card card"
          @click="viewPokemonDetail(pokemon.name)"
        >
          <div class="pokemon-image">
            <img 
              :src="getPokemonImage(pokemon.url)" 
              :alt="pokemon.name"
              @error="handleImageError"
            />
          </div>
          
          <h3 class="pokemon-name">{{ capitalize(pokemon.name) }}</h3>
          
          <button 
            @click.stop="toggleFavorite(pokemon)"
            class="favorite-btn"
            :class="{ 'favorited': isFavorite(pokemon.name) }"
          >
            ⭐ {{ isFavorite(pokemon.name) ? 'Favorited' : 'Add to Favorites' }}
          </button>
        </div>
      </div>

      <!-- Pagination Info -->
      <div class="pagination-footer">
        <p>
          Showing {{ (currentPage - 1) * limit + 1 }}-{{ Math.min(currentPage * limit, totalCount) }} 
          of {{ totalCount.toLocaleString() }} Pokémon
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { pokemonService } from '@/services/api';
import { capitalize } from '@/utils/helpers';
import type { Pokemon, FavoritePokemon } from '@/types/pokemon';

const router = useRouter();

const pokemons = ref<Pokemon[]>([]);
const favorites = ref<FavoritePokemon[]>([]);
const currentPage = ref(1);
const limit = ref(20);
const totalCount = ref(0);
const totalPages = ref(0);
const loading = ref(false);
const error = ref<string | null>(null);

const loadPokemons = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await pokemonService.getPokemons(currentPage.value, limit.value);
    pokemons.value = response.data;
    totalCount.value = response.pagination.total;
    totalPages.value = response.pagination.last_page;
  } catch (err) {
    error.value = (err as Error).message;
  } finally {
    loading.value = false;
  }
};

const loadFavorites = async () => {
  try {
    const response = await pokemonService.getFavorites();
    favorites.value = response.data;
  } catch (err) {
    console.error('Failed to load favorites:', err);
  }
};

// Define props and emits
const props = defineProps<{
  favoritesCount: number;
}>();

const emit = defineEmits<{
  'update-favorites': [number];
}>();

const toggleFavorite = async (pokemon: Pokemon | FavoritePokemon) => {
  try {
    const response = await pokemonService.toggleFavorite(pokemon.name);
    await loadFavorites(); // Reload local favorites
    
    // Calculate new count based on current action
    const newCount = response.is_favorite 
      ? props.favoritesCount + 1 
      : props.favoritesCount - 1;
    
    // Emit the new count to parent
    emit('update-favorites', newCount);
    
  } catch (err) {
    console.error('Failed to toggle favorite:', err);
  }
};

const isFavorite = (pokemonName: string): boolean => {
  return favorites.value.some(fav => fav.name === pokemonName);
};

const getPokemonImage = (url: string): string => {
  const id = url.split('/').filter(Boolean).pop();
  return `https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/${id}.png`;
};

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement;
  target.src = 'https://via.placeholder.com/96x96?text=Pokémon';
};

const viewPokemonDetail = (pokemonName: string) => {
  router.push(`/pokemon/${pokemonName}`);
};

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    loadPokemons();
  }
};

const handleLimitChange = () => {
  currentPage.value = 1;
  loadPokemons();
};

onMounted(async () => {
  await loadPokemons();
  await loadFavorites();
});
</script>
