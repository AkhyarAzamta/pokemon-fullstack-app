// services/api.ts
import axios from 'axios';
import type { 
  PokemonListResponse, 
  PokemonDetailResponse, 
  FavoriteResponse, 
  AbilitiesResponse, 
  ToggleFavoriteResponse 
} from '@/types/pokemon';

// Gunakan base URL dari environment variable, fallback ke localhost
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'https://api-pokemon-app.akhyarazamta.com';

const api = axios.create({
  baseURL: `${API_BASE_URL}/api`,
  timeout: 10000,
});

// Enhanced error handling dengan logging
api.interceptors.response.use(
  (response) => {
    console.log('API Response:', response.config.url, response.data);
    return response;
  },
  (error) => {
    console.error('API Error:', error.config?.url, error.response?.data);
    
    let errorMessage = 'An unexpected error occurred';
    
    if (error.code === 'ECONNABORTED') {
      errorMessage = 'Request timeout. Please try again.';
    } else if (error.response?.status === 404) {
      errorMessage = 'Resource not found';
    } else if (error.response?.status === 422) {
      const errors = error.response.data.messages || error.response.data.errors;
      errorMessage = Object.values(errors).flat().join(', ') || 'Validation failed';
    } else if (error.response?.status >= 500) {
      errorMessage = 'Server error. Please try again later.';
    } else if (error.response?.data?.error) {
      errorMessage = error.response.data.error;
    } else if (error.message) {
      errorMessage = error.message;
    }
    
    throw new Error(errorMessage);
  }
);

export const pokemonService = {
  async getPokemons(page: number = 1, limit: number = 20): Promise<PokemonListResponse> {
    const response = await api.get(`/pokemon?page=${page}&limit=${limit}`);
    return response.data;
  },

  async getPokemonDetail(identifier: string | number): Promise<PokemonDetailResponse> {
    const response = await api.get(`/pokemon/${identifier}`);
    return response.data;
  },

  async toggleFavorite(identifier: string | number): Promise<ToggleFavoriteResponse> {
    const response = await api.post(`/pokemon/${identifier}/favorite`);
    return response.data;
  },

  async getFavorites(): Promise<FavoriteResponse> {
    const response = await api.get('/favorites');
    console.log('Favorites API Response:', response.data);
    return response.data;
  },

  async searchFavorites(query: string): Promise<FavoriteResponse> {
    const response = await api.get(`/favorites/search?q=${encodeURIComponent(query)}`);
    return response.data;
  },

  async getFavoriteAbilities(): Promise<AbilitiesResponse> {
    const response = await api.get('/favorites/abilities');
    return response.data;
  },

  async getPokemonByAbility(ability: string): Promise<FavoriteResponse> {
    const response = await api.get(`/favorites/ability/${encodeURIComponent(ability)}`);
    return response.data;
  },
};

// Tambahkan service untuk coin counter
export interface CoinCountResponse {
  coins: string[];
}

export interface CoinCountError {
  message: string;
  errors?: Record<string, string[]>;
}

export const coinService = {
  async countCoins(coins: number[]): Promise<CoinCountResponse> {
    const response = await api.post('/coins/count', { coins });
    return response.data;
  },
};