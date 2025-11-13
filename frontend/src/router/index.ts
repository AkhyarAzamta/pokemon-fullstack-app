import { createRouter, createMemoryHistory } from 'vue-router'
import PokemonList from '../components/PokemonList.vue'
import PokemonDetail from '../components/PokemonDetail.vue'
import PokemonFavorites from '../components/PokemonFavorites.vue'
import PokemonAbilities from '../components/PokemonAbilities.vue'
import CoinCounter from '@/components/CoinCounter.vue'

const router = createRouter({
  history: createMemoryHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: PokemonList
    },
    {
      path: '/pokemon/:id',
      name: 'pokemon-detail',
      component: PokemonDetail,
      props: true
    },
    {
      path: '/favorites',
      name: 'favorites',
      component: PokemonFavorites
    },
    {
      path: '/abilities',
      name: 'abilities',
      component: PokemonAbilities
    },
      {
    path: '/coins',
    name: 'Coins',
    component: CoinCounter
  }
  ]
})

export default router