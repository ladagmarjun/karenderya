<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { create, index } from '@/routes/orders';
import type { Order, Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Orders', href: index() }],
    },
});

defineProps<{ orders: Paginated<Order> }>();
</script>

<template>
    <Head title="Orders" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <Heading
                title="Orders"
                description="Previous orders, newest first."
            />
            <Button as-child>
                <Link :href="create()">New order</Link>
            </Button>
        </div>

        <p
            v-if="orders.data.length === 0"
            class="text-sm text-muted-foreground"
        >
            No orders yet.
        </p>

        <div v-else class="overflow-x-auto rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left text-muted-foreground">
                    <tr>
                        <th class="px-4 py-2 font-medium">Order #</th>
                        <th class="px-4 py-2 font-medium">Date</th>
                        <th class="px-4 py-2 font-medium">Items</th>
                        <th class="px-4 py-2 text-right font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="order in orders.data" :key="order.id">
                        <td class="px-4 py-2 font-medium">{{ order.id }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ new Date(order.created_at).toLocaleString() }}
                        </td>
                        <td class="px-4 py-2">
                            <div v-for="line in order.items" :key="line.id">
                                {{ line.quantity }} ×
                                {{ line.food_item.name }}
                                <span class="text-muted-foreground">
                                    (₱{{ line.unit_price }} each)
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-right font-semibold">
                            ₱{{ order.total }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="orders.last_page > 1"
            class="flex items-center justify-between text-sm"
        >
            <span class="text-muted-foreground">
                Page {{ orders.current_page }} of
                {{ orders.last_page }} &middot; {{ orders.total }} orders
            </span>
            <div class="flex gap-2">
                <Button
                    v-if="orders.prev_page_url"
                    variant="outline"
                    size="sm"
                    as-child
                >
                    <Link :href="orders.prev_page_url">Previous</Link>
                </Button>
                <Button v-else variant="outline" size="sm" disabled>
                    Previous
                </Button>

                <Button
                    v-if="orders.next_page_url"
                    variant="outline"
                    size="sm"
                    as-child
                >
                    <Link :href="orders.next_page_url">Next</Link>
                </Button>
                <Button v-else variant="outline" size="sm" disabled>
                    Next
                </Button>
            </div>
        </div>
    </div>
</template>
