export type FoodItem = {
    id: number;
    name: string;
    price: number;
    stock: number;
};

export type OrderItem = {
    id: number;
    quantity: number;
    unit_price: number;
    food_item: FoodItem;
};

// The fields we use from Laravel's paginate() response.
export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

export type Order = {
    id: number;
    total: number;
    created_at: string;
    items: OrderItem[];
};
