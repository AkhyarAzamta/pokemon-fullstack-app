<template>
  <div class="page-container">
    <div class="page-header">
      <h1>Coin Counter</h1>
      <p>Count and sort your coins by frequency</p>
    </div>

    <div class="coin-counter-container">
      <div class="input-section card">
        <h2>Input Coins</h2>
        <p class="description">Enter coin values as a JSON array (e.g., [50, 100, 50, 200])</p>
        
        <div class="input-group">
          <label for="coins-input">Coin Values:</label>
          <textarea
            id="coins-input"
            v-model="coinsInput"
            placeholder="[50, 1000, 400, 50, 300, 1200, 1000, 25, 50, 45, 100]"
            rows="6"
            class="coins-textarea"
            :class="{ 'error': inputError }"
          ></textarea>
          <div v-if="inputError" class="error-message">{{ inputError }}</div>
        </div>

        <div class="examples">
          <h3>Example Inputs:</h3>
          <div class="example-buttons">
            <button 
              v-for="example in examples" 
              :key="example.name"
              @click="loadExample(example.data)"
              class="btn btn-outline example-btn"
            >
              {{ example.name }}
            </button>
          </div>
        </div>

        <button 
          @click="countCoins" 
          :disabled="!coinsInput || loading"
          class="btn btn-primary count-btn"
        >
          {{ loading ? 'Counting...' : 'Count Coins' }}
        </button>
      </div>

      <div class="results-section card" v-if="results || error">
        <h2>Results</h2>
        
        <div v-if="loading" class="loading-container">
          <div class="loading"></div>
          <p>Processing coins...</p>
        </div>

        <div v-else-if="error" class="error-container">
          <h3>⚠️ Error</h3>
          <p>{{ error }}</p>
          <button @click="clearError" class="btn btn-primary">Try Again</button>
        </div>

        <div v-else-if="results" class="results-content">
          <div class="summary">
            <p><strong>Total Coins:</strong> {{ totalCoins }}</p>
            <p><strong>Unique Values:</strong> {{ results.length }}</p>
          </div>
          
          <div class="coins-grid">
            <div 
              v-for="(coin, index) in results" 
              :key="index"
              class="coin-item"
            >
              <div class="coin-value">{{ coin }}</div>
            </div>
          </div>

          <div class="sorted-info">
            <p>Coins are sorted by frequency (most common first)</p>
          </div>
        </div>
      </div>

      <div class="info-section card" v-else>
        <div class="info-content">
          <div class="info-icon">💡</div>
          <h3>How it works</h3>
          <ul>
            <li>Enter coin values as a JSON array (e.g., <code>[50, 100, 50]</code>)</li>
            <li>The API will count occurrences of each coin value</li>
            <li>Results are sorted by frequency (most common first)</li>
            <li>Format: <code>"countx value"</code> (e.g., "2x 50")</li>
          </ul>
          <div class="api-info">
            <h4>API Endpoint:</h4>
            <code>POST /api/coins/count</code>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { coinService, type CoinCountResponse } from '@/services/api';

interface ExampleData {
  name: string;
  data: number[];
}

const coinsInput = ref<string>('');
const results = ref<string[] | null>(null);
const loading = ref<boolean>(false);
const error = ref<string | null>(null);
const inputError = ref<string | null>(null);

const examples: ExampleData[] = [
  {
    name: 'Sample 1',
    data: [50, 1000, 400, 50, 300, 1200, 1000, 25, 50, 45, 100]
  },
  {
    name: 'Sample 2',
    data: [100, 200, 100, 50, 25, 10, 5, 100, 50]
  },
  {
    name: 'Empty Array',
    data: []
  },
  {
    name: 'Single Value',
    data: [500, 500, 500]
  }
];

const totalCoins = computed(() => {
  if (!coinsInput.value) return 0;
  try {
    const coins = JSON.parse(coinsInput.value);
    return Array.isArray(coins) ? coins.length : 0;
  } catch {
    return 0;
  }
});

const validateInput = (): boolean => {
  inputError.value = null;

  if (!coinsInput.value.trim()) {
    inputError.value = 'Please enter coin values';
    return false;
  }

  try {
    const parsed = JSON.parse(coinsInput.value);
    
    if (!Array.isArray(parsed)) {
      inputError.value = 'Input must be a JSON array';
      return false;
    }

    // Allow empty array for testing
    if (parsed.length === 0) {
      return true;
    }

    const invalidItems = parsed.filter(item => 
      typeof item !== 'number' || !Number.isInteger(item) || item < 0
    );

    if (invalidItems.length > 0) {
      inputError.value = 'All coin values must be positive integers';
      return false;
    }

    return true;
  } catch (e) {
    console.error('Error parsing JSON:', e);
    inputError.value = 'Invalid JSON format';
    return false;
  }
};

const loadExample = (exampleData: number[]) => {
  coinsInput.value = JSON.stringify(exampleData, null, 2);
  inputError.value = null;
  error.value = null;
  results.value = null;
};

const countCoins = async () => {
  if (!validateInput()) return;

  loading.value = true;
  error.value = null;
  results.value = null;

  try {
    const coins = JSON.parse(coinsInput.value);
    
    // Gunakan service yang sudah diperbaiki
    const response: CoinCountResponse = await coinService.countCoins(coins);
    results.value = response.coins;
    
    console.log('Coin count results:', response);
  } catch (err) {
    error.value = (err as Error).message;
    console.error('Error counting coins:', err);
  } finally {
    loading.value = false;
  }
};

const clearError = () => {
  error.value = null;
  results.value = null;
};
</script>

<!-- Styles tetap sama seperti sebelumnya -->

<style scoped>
.coin-counter-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.input-section,
.results-section,
.info-section {
  height: fit-content;
}

.description {
  color: var(--gray-600);
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
}

.input-group {
  margin-bottom: 1.5rem;
}

.input-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: var(--gray-700);
}

.coins-textarea {
  width: 100%;
  padding: 1rem;
  border: 2px solid var(--gray-300);
  border-radius: var(--radius-md);
  font-family: 'Courier New', monospace;
  font-size: 0.9rem;
  resize: vertical;
  transition: border-color 0.3s ease;
}

.coins-textarea:focus {
  outline: none;
  border-color: var(--primary);
}

.coins-textarea.error {
  border-color: var(--danger);
}

.error-message {
  color: var(--danger);
  font-size: 0.8rem;
  margin-top: 0.5rem;
}

.examples {
  margin-bottom: 1.5rem;
}

.examples h3 {
  font-size: 1rem;
  margin-bottom: 0.5rem;
  color: var(--gray-700);
}

.example-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.example-btn {
  font-size: 0.8rem;
  padding: 0.5rem 0.75rem;
}

.count-btn {
  width: 100%;
  padding: 1rem;
  font-size: 1.1rem;
}

.results-content {
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.summary {
  display: flex;
  gap: 2rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: var(--gray-50);
  border-radius: var(--radius-md);
}

.summary p {
  margin: 0;
  font-size: 0.9rem;
}

.coins-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.coin-item {
  background: linear-gradient(135deg, var(--primary-light), var(--primary));
  color: white;
  padding: 1rem 0.5rem;
  border-radius: var(--radius-lg);
  text-align: center;
  font-weight: 600;
  box-shadow: var(--shadow-md);
  transition: transform 0.2s ease;
}

.coin-item:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.coin-value {
  font-size: 0.9rem;
  word-break: break-word;
}

.sorted-info {
  text-align: center;
  color: var(--gray-600);
  font-size: 0.8rem;
  margin-top: 1rem;
}

.info-content {
  text-align: center;
}

.info-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.info-content h3 {
  margin-bottom: 1rem;
  color: var(--gray-800);
}

.info-content ul {
  text-align: left;
  margin: 1rem 0;
  padding-left: 1.5rem;
}

.info-content li {
  margin-bottom: 0.5rem;
  color: var(--gray-700);
}

.info-content code {
  background: var(--gray-100);
  padding: 0.2rem 0.4rem;
  border-radius: var(--radius-sm);
  font-family: 'Courier New', monospace;
  font-size: 0.8rem;
}

.api-info {
  margin-top: 1.5rem;
  padding: 1rem;
  background: var(--gray-50);
  border-radius: var(--radius-md);
}

.api-info h4 {
  margin-bottom: 0.5rem;
  color: var(--gray-700);
}

/* Responsive Design */
@media (max-width: 768px) {
  .coin-counter-container {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .summary {
    flex-direction: column;
    gap: 0.5rem;
  }

  .coins-grid {
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
  }

  .example-buttons {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .coins-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .coin-item {
    padding: 0.75rem 0.25rem;
    font-size: 0.8rem;
  }
}
</style>