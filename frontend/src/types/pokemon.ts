// types/pokemon.ts
export interface Pokemon {
  name: string;
  url: string;
}

export interface PokemonDetail {
  id: number;
  name: string;
  height: number;
  weight: number;
  types: string[];
  abilities: Array<{
    name: string;
    is_hidden: boolean;
  }>;
  stats: Array<{
    name: string;
    value: number;
  }>;
  sprite: string;
  is_favorite: boolean;
}

export interface Ability {
  name: string;
  is_hidden?: boolean;
  ability?: {
    name: string;
    url: string;
  };
}

export interface Type {
  name: string;
  type?: {
    name: string;
    url: string;
  };
}

export interface Stat {
  name: string;
  value?: number;
  base_stat?: number;
  stat?: {
    name: string;
    url: string;
  };
}

export interface FavoritePokemon {
  id: number;
  pokeapi_id: number;
  name: string;
  types: (string | Type)[];
  abilities: Ability[];
  stats: Stat[];
  sprite: string;
  height: number;
  weight: number;
  is_favorite: boolean;
  created_at: string;
  updated_at: string;
}

export interface Pagination {
  current_page: number;
  total: number;
  per_page: number;
  last_page: number;
}

export interface PokemonListResponse {
  data: Pokemon[];
  pagination: Pagination;
}

export interface PokemonDetailResponse {
  data: PokemonDetail;
}

export interface FavoriteResponse {
  data: FavoritePokemon[];
  count: number;
}

export interface AbilitiesResponse {
  data: string[];
  count: number;
}

export interface ToggleFavoriteResponse {
  message: string;
  data?: FavoritePokemon;
  is_favorite: boolean;
}

export interface RawPokemonDetail {
  id: number;
  name: string;
  height: number;
  weight: number;
  types: Array<{
    slot?: number;
    type?: {
      name: string;
      url: string;
    };
    name?: string;
  } | string>;
  abilities: Array<{
    ability?: {
      name: string;
      url: string;
    };
    name?: string;
    is_hidden?: boolean;
    slot?: number;
  }>;
  stats: Array<{
    base_stat?: number;
    effort?: number;
    value?: number;
    stat?: {
      name: string;
      url: string;
    };
    name?: string;
  }>;
  sprite?: string;
  sprites?: {
    front_default: string;
    [key: string]: string | undefined;
  };
  is_favorite?: boolean;
}