<template>
  <div class="page-container">
    <div class="page-header">
      <h1>⭐ Favorite Pokémon</h1>
      <p>Your collection of favorite Pokémon</p>
    </div>

    <!-- Search Bar -->
    <div class="search-container">
      <div class="search-box">
        <input
          v-model="searchQuery"
          @input="handleSearch"
          type="text"
          placeholder="Search your favorite Pokémon..."
          class="search-input"
        />
        <button 
          @click="clearSearch" 
          class="clear-btn"
          :disabled="!searchQuery"
        >
          ×
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading"></div>
      <p>Loading favorites...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <h3>⚠️ Error Loading Favorites</h3>
      <p>{{ error }}</p>
      <button @click="loadFavorites" class="btn btn-primary">Try Again</button>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredFavorites.length === 0" class="empty-state">
      <div class="empty-icon">⭐</div>
      <h3>No Favorite Pokémon Found</h3>
      <p v-if="searchQuery">No results found for "{{ searchQuery }}"</p>
      <p v-else>Start by adding some Pokémon to your favorites!</p>
      <button @click="goToHome" class="btn btn-primary">
        Explore Pokémon
      </button>
    </div>

    <!-- Favorites Grid -->
    <div v-else>
      <div class="results-info">
        <p>
          Showing {{ filteredFavorites.length }} favorite Pokémon{{ filteredFavorites.length !== 1 ? 's' : '' }}
          <span v-if="searchQuery">matching "{{ searchQuery }}"</span>
        </p>
      </div>

      <div class="favorites-grid">
        <div 
          v-for="pokemon in filteredFavorites" 
          :key="pokemon.id"
          class="favorite-card card"
        >
          <div class="pokemon-image">
            <img 
              :src="getValidSprite(pokemon.sprite)" 
              :alt="pokemon.name"
              @error="handleImageError"
              @click="viewPokemonDetail(pokemon.name)"
            />
          </div>
          
          <h3 class="pokemon-name">{{ capitalize(pokemon.name) }}</h3>
          
          <!-- Types -->
          <div class="types-container">
            <span 
              v-for="type in getValidTypes(pokemon.types)" 
              :key="type"
              class="type-badge small"
              :style="{ backgroundColor: getTypeColor(type) }"
            >
              {{ capitalize(type) }}
            </span>
          </div>

          <!-- Abilities Preview -->
          <div class="abilities-preview">
            <span class="label">Abilities:</span>
            <div class="abilities-list">
              <span 
                v-for="ability in getValidAbilities(pokemon.abilities).slice(0, 2)" 
                :key="ability.name"
                class="ability-badge"
              >
                {{ capitalize(ability.name) }}
              </span>
              <span v-if="getValidAbilities(pokemon.abilities).length > 2" class="more-badge">
                +{{ getValidAbilities(pokemon.abilities).length - 2 }}
              </span>
            </div>
          </div>

          <div class="actions">
            <button 
              @click="viewPokemonDetail(pokemon.name)"
              class="btn btn-outline"
            >
              View Details
            </button>
            <button 
              @click="confirmRemove(pokemon)"
              class="btn btn-danger"
            >
              Remove
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { pokemonService } from '@/services/api';
import { capitalize, getTypeColor } from '@/utils/helpers';
import type { FavoritePokemon } from '@/types/pokemon';

const router = useRouter();

const favorites = ref<FavoritePokemon[]>([]);
const searchQuery = ref('');
const loading = ref(false);
const error = ref<string | null>(null);
const searchTimeout = ref<number | null>(null);

// Define props and emits
const props = defineProps<{
  favoritesCount: number;
}>();

const emit = defineEmits<{
  'update-favorites': [number];
}>();

// Helper functions
const getValidSprite = (sprite: string | null | undefined): string => {
  return sprite && typeof sprite === 'string' 
    ? sprite 
    : 'https://via.placeholder.com/120x120?text=Pokémon';
};

const getValidTypes = (types: FavoritePokemon['types'] | null | undefined): string[] => {
  if (!types || !Array.isArray(types)) {
    return ['unknown'];
  }
  
  return types.map(type => {
    if (typeof type === 'string') {
      return type;
    } else if (type && typeof type === 'object') {
      return type.type?.name || type.name || 'unknown';
    }
    return 'unknown';
  }).filter(type => type !== 'unknown');
};

const getValidAbilities = (abilities: FavoritePokemon['abilities'] | null | undefined): Array<{name: string}> => {
  if (!abilities || !Array.isArray(abilities)) {
    return [];
  }
  
  return abilities.map(ability => {
    let abilityName = 'unknown';
    
    if (ability && typeof ability === 'object') {
      if (ability.name && typeof ability.name === 'string') {
        abilityName = ability.name;
      } else if (ability.ability && ability.ability.name && typeof ability.ability.name === 'string') {
        abilityName = ability.ability.name;
      }
    }
    
    return { name: abilityName };
  }).filter(ability => ability.name !== 'unknown');
};

// Computed property untuk filtered favorites
const filteredFavorites = computed(() => {
  if (!searchQuery.value.trim()) {
    return favorites.value;
  }
  
  const query = searchQuery.value.toLowerCase();
  return favorites.value.filter(pokemon => 
    pokemon.name.toLowerCase().includes(query)
  );
});

const loadFavorites = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await pokemonService.getFavorites();
    favorites.value = response.data || [];
  } catch (err) {
    console.error('Error loading favorites:', err);
    error.value = (err as Error).message;
    favorites.value = [];
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }
  
  searchTimeout.value = setTimeout(() => {
    // Tidak perlu API call lagi karena menggunakan computed property
  }, 300);
};

const clearSearch = () => {
  searchQuery.value = '';
};

const confirmRemove = (pokemon: FavoritePokemon) => {
  const pokemonName = capitalize(pokemon.name);
  const confirmed = window.confirm(`Are you sure you want to remove ${pokemonName} from your favorites?`);
  
  if (confirmed) {
    removeFromFavorites(pokemon);
  }
};

const removeFromFavorites = async (pokemon: FavoritePokemon) => {
  try {
    await pokemonService.toggleFavorite(pokemon.name);
    
    // Remove from local list immediately for better UX
    favorites.value = favorites.value.filter(fav => fav.id !== pokemon.id);
    
    // Emit the new count (current count - 1)
    emit('update-favorites', props.favoritesCount - 1);
    
    console.log(`Removed ${pokemon.name} from favorites`);
  } catch (err) {
    console.error('Failed to remove favorite:', err);
    error.value = 'Failed to remove from favorites';
    // Reload if there was an error to get correct state
    await loadFavorites();
  }
};

const viewPokemonDetail = (pokemonName: string) => {
  if (pokemonName && typeof pokemonName === 'string') {
    router.push(`/pokemon/${pokemonName}`);
  }
};

const goToHome = () => {
  router.push('/');
};

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement;
  target.src = 'https://via.placeholder.com/120x120?text=Pokémon';
};

onMounted(() => {
  loadFavorites();
});
</script>