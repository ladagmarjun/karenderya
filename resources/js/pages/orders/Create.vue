<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { index as foodItemsIndex } from '@/routes/food-items';
import { create, index } from '@/routes/orders';
import type { FoodItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Orders', href: index() },
            { title: 'New order', href: create() },
        ],
    },
});

const props = defineProps<{ foodItems: FoodItem[] }>();

// Quantity per food item id. Only items with a quantity above 0 are sent.
const quantities = reactive<Record<number, number>>({});

const lines = computed(() =>
    props.foodItems
        .filter((item) => (quantities[item.id] ?? 0) > 0)
        .map((item) => ({ item, quantity: quantities[item.id] })),
);

// Display only. The server recalculates the total from its own prices.
const total = computed(() =>
    lines.value.reduce((sum, line) => sum + line.item.price * line.quantity, 0),
);

const form = useForm({
    items: [] as { food_item_id: number; quantity: number }[],
});

function submit() {
    form.transform(() => ({
        items: lines.value.map((line) => ({
            food_item_id: line.item.id,
            quantity: line.quantity,
        })),
    })).submit(OrderController.store());
}
</script>

<template>
    <Head title="New order" />

    <form class="flex max-w-2xl flex-col gap-6 p-4" @submit.prevent="submit">
        <Heading
            title="New order"
            description="Enter how many of each item the customer wants."
        />

        <p v-if="foodItems.length === 0" class="text-sm text-muted-foreground">
            The menu is empty.
            <Link :href="foodItemsIndex()" class="underline">
                Add food items first.
            </Link>
        </p>

        <div v-else class="flex flex-col divide-y rounded-lg border">
            <div
                v-for="item in foodItems"
                :key="item.id"
                class="flex items-center justify-between gap-4 p-3"
                :class="item.stock === 0 ? 'opacity-50' : ''"
            >
                <div>
                    <div class="font-medium">{{ item.name }}</div>
                    <div class="text-sm text-muted-foreground">
                        ₱{{ item.price }} ·
                        {{
                            item.stock === 0 ? 'Sold out' : `${item.stock} left`
                        }}
                    </div>
                </div>
                <Input
                    v-model.number="quantities[item.id]"
                    type="number"
                    min="0"
                    :max="item.stock"
                    placeholder="0"
                    class="w-24"
                    :aria-label="`Quantity of ${item.name}`"
                    :disabled="item.stock === 0"
                />
            </div>
        </div>

        <InputError :message="form.errors.items" />

        <div class="flex items-center justify-between">
            <div class="text-lg font-semibold">Total: ₱{{ total }}</div>
            <Button :disabled="form.processing || lines.length === 0">
                Place order
            </Button>
        </div>
    </form>
</template>
