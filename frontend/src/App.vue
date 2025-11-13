<template>
  <div id="app">
    <nav class="navbar">
      <div class="nav-container">
        <router-link to="/" class="nav-brand">
          🐉 Pokemon App
        </router-link>
        <div class="nav-links">
          <router-link to="/" class="nav-link">Home</router-link>
          <router-link to="/favorites" class="nav-link">
            Favorites
            <span v-if="favoritesCount > 0" class="badge">{{ favoritesCount }}</span>
          </router-link>
          <router-link to="/abilities" class="nav-link">Abilities</router-link>
          <router-link to="/coins" class="nav-link">Coin Counter</router-link>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <router-view :favorites-count="favoritesCount" @update-favorites="handleUpdateFavorites" />
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { pokemonService } from '@/services/api';

const favoritesCount = ref(0);

const loadFavoritesCount = async () => {
  try {
    const response = await pokemonService.getFavorites();
    favoritesCount.value = response.count;
  } catch (error) {
    console.error('Failed to load favorites count:', error);
  }
};

const handleUpdateFavorites = (newCount: number) => {
  favoritesCount.value = newCount;
};

onMounted(() => {
  loadFavoritesCount();
});
</script>
