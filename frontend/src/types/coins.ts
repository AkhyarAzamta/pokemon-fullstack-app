export interface CoinCountRequest {
  coins: number[];
}

export interface CoinCountResponse {
  coins: string[];
  message: string;
}

export interface CoinCountError {
  message: string;
  errors?: Record<string, string[]>;
}

export interface ExampleData {
  name: string;
  data: number[];
}