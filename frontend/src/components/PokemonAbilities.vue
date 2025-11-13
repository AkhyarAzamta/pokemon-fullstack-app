<template>
  <div class="page-container">
    <div class="page-header">
      <h1>⚡ Pokémon Abilities</h1>
      <p>Explore abilities from your favorite Pokémon</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading"></div>
      <p>Loading abilities...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <h3>⚠️ Error Loading Abilities</h3>
      <p>{{ error }}</p>
      <button @click="loadAbilities" class="btn btn-primary">Try Again</button>
    </div>

    <!-- Content -->
    <div v-else>
      <!-- Abilities List -->
      <div class="abilities-section card">
        <div class="section-header">
          <h2>All Abilities</h2>
          <span class="count-badge">{{ abilities.length }} abilities</span>
        </div>

        <!-- Empty State -->
        <div v-if="abilities.length === 0" class="empty-abilities">
          <div class="empty-icon">⚡</div>
          <h3>No Abilities Found</h3>
          <p>Add some Pokémon to favorites to see their abilities!</p>
          <button @click="goToHome" class="btn btn-primary">
            Explore Pokémon
          </button>
        </div>

        <!-- Abilities Grid -->
        <div v-else class="abilities-grid">
          <button
            v-for="ability in abilities"
            :key="ability"
            @click="selectAbility(ability)"
            class="ability-card"
            :class="{ 'selected': selectedAbility === ability }"
          >
            <span class="ability-name">{{ capitalize(ability) }}</span>
            <span class="ability-count" v-if="abilityCounts[ability]">
              {{ abilityCounts[ability] }}
            </span>
          </button>
        </div>
      </div>

      <!-- Selected Ability Details -->
      <div v-if="selectedAbility" class="ability-details">
        <div class="section-header">
          <h2>
            Pokémon with 
            <span class="ability-highlight">"{{ capitalize(selectedAbility) }}"</span>
          </h2>
          <button @click="clearSelection" class="btn btn-primary">
            Clear Selection
          </button>
        </div>

        <!-- Loading State for Ability Pokémon -->
        <div v-if="loadingAbility" class="loading-container">
          <div class="loading"></div>
          <p>Loading Pokémon with {{ selectedAbility }} ability...</p>
        </div>

        <!-- No Pokémon Found -->
        <div v-else-if="abilityPokemons.length === 0" class="no-pokemon">
          <div class="empty-icon">🔍</div>
          <h3>No Pokémon Found</h3>
          <p>No favorite Pokémon have the "{{ selectedAbility }}" ability</p>
        </div>

        <!-- Pokémon Grid -->
        <div v-else class="pokemon-grid grid grid-cols-4">
          <div 
            v-for="pokemon in abilityPokemons" 
            :key="pokemon.id"
            class="pokemon-card card"
            @click="viewPokemonDetail(pokemon.name)"
          >
            <div class="pokemon-image">
              <img 
                :src="pokemon.sprite" 
                :alt="pokemon.name"
                @error="handleImageError"
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

            <!-- Stats Preview -->
            <div class="stats-preview">
              <div class="stat">
                <span class="stat-label">HP</span>
                <span class="stat-value">
                  {{ getStatValue(pokemon.stats, 'hp') }}
                </span>
              </div>
              <div class="stat">
                <span class="stat-label">ATK</span>
                <span class="stat-value">
                  {{ getStatValue(pokemon.stats, 'attack') }}
                </span>
              </div>
            </div>

            <button class="view-btn">
              View Details →
            </button>
          </div>
        </div>
      </div>

      <!-- Instructions -->
      <div v-else class="instructions card">
        <div class="instructions-content">
          <div class="instructions-icon">🎯</div>
          <h3>Select an Ability</h3>
          <p>
            Click on any ability above to see which of your favorite Pokémon 
            have that ability. This helps you build teams with specific ability synergies!
          </p>
          <div class="tips">
            <div class="tip">
              <strong>💡 Tip:</strong> Some abilities like "static" or "intimidate" 
              can give your team strategic advantages in battles.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { pokemonService } from '@/services/api';
import { capitalize, getTypeColor } from '@/utils/helpers';
import type { FavoritePokemon, Stat } from '@/types/pokemon';

const router = useRouter();

const abilities = ref<string[]>([]);
const selectedAbility = ref<string>('');
const abilityPokemons = ref<FavoritePokemon[]>([]);
const allFavorites = ref<FavoritePokemon[]>([]);
const loading = ref(false);
const loadingAbility = ref(false);
const error = ref<string | null>(null);

// Helper function to handle different type structures
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

// Compute ability counts from all favorites
const abilityCounts = computed(() => {
  const counts: { [key: string]: number } = {};
  
  allFavorites.value.forEach(pokemon => {
    const validAbilities = getValidAbilities(pokemon.abilities);
    validAbilities.forEach(ability => {
      counts[ability.name] = (counts[ability.name] || 0) + 1;
    });
  });
  
  return counts;
});

// Helper function untuk abilities
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

// Improved function to handle different stat structures
const getStatValue = (stats: Stat[] | null | undefined, statName: string): number => {
  if (!stats || !Array.isArray(stats)) {
    console.log('No stats found for', statName);
    return 0;
  }

  // Cari stat berdasarkan nama
  const stat = stats.find(s => {
    // Handle berbagai format stat name
    const currentStatName = s.stat?.name || s.name;
    return currentStatName?.toLowerCase() === statName.toLowerCase();
  });

  if (!stat) {
    console.log(`Stat ${statName} not found in:`, stats.map(s => s.stat?.name || s.name));
    return 0;
  }

  // Ambil value dari berbagai format
  const value = stat.base_stat || stat.value || 0;
  console.log(`Found ${statName}:`, value, 'from stat:', stat);
  return value;
};

const loadAbilities = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await pokemonService.getFavoriteAbilities();
    abilities.value = response.data;
    
    // Also load all favorites for counting
    const favoritesResponse = await pokemonService.getFavorites();
    allFavorites.value = favoritesResponse.data;
    console.log('Loaded favorites for abilities:', allFavorites.value);
  } catch (err) {
    error.value = (err as Error).message;
  } finally {
    loading.value = false;
  }
};

const selectAbility = async (ability: string) => {
  selectedAbility.value = ability;
  loadingAbility.value = true;
  
  try {
    const response = await pokemonService.getPokemonByAbility(ability);
    abilityPokemons.value = response.data;
    console.log(`Pokémon with ability ${ability}:`, abilityPokemons.value);
  } catch (err) {
    console.error('Failed to load ability Pokémon:', err);
    abilityPokemons.value = [];
  } finally {
    loadingAbility.value = false;
  }
};

const clearSelection = () => {
  selectedAbility.value = '';
  abilityPokemons.value = [];
};

const viewPokemonDetail = (pokemonName: string) => {
  router.push(`/pokemon/${pokemonName}`);
};

const goToHome = () => {
  router.push('/');
};

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement;
  target.src = 'https://via.placeholder.com/120x120?text=Pokémon';
};

onMounted(() => {
  loadAbilities();
});
</script>