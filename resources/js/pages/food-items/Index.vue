<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import FoodItemController from '@/actions/App/Http/Controllers/FoodItemController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/food-items';
import type { FoodItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Menu', href: index() }],
    },
});

defineProps<{ foodItems: FoodItem[] }>();
</script>

<template>
    <Head title="Menu" />

    <div class="flex flex-col gap-8 p-4">
        <section>
            <Heading
                title="Menu"
                description="Food items, prices, and how many servings are left. Set stock to 0 to mark an item as sold out."
            />

            <p
                v-if="foodItems.length === 0"
                class="text-sm text-muted-foreground"
            >
                No food items yet. Add one below.
            </p>

            <div v-else class="flex flex-col gap-2">
                <div
                    class="hidden grid-cols-[1fr_8rem_8rem_5rem] gap-2 px-1 text-sm font-medium text-muted-foreground md:grid"
                >
                    <span>Name</span>
                    <span>Price (₱)</span>
                    <span>Stock</span>
                    <span></span>
                </div>

                <!-- One small form per row so each item is edited and saved on its own. -->
                <Form
                    v-for="item in foodItems"
                    :key="item.id"
                    v-bind="FoodItemController.update.form(item.id)"
                    :options="{ preserveScroll: true }"
                    class="grid gap-2 md:grid-cols-[1fr_8rem_8rem_5rem]"
                    v-slot="{ errors, processing }"
                >
                    <div>
                        <Input
                            name="name"
                            :default-value="item.name"
                            aria-label="Name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <div>
                        <Input
                            name="price"
                            type="number"
                            min="1"
                            :default-value="item.price"
                            aria-label="Price"
                            required
                        />
                        <InputError :message="errors.price" />
                    </div>
                    <div>
                        <Input
                            name="stock"
                            type="number"
                            min="0"
                            :default-value="item.stock"
                            aria-label="Stock"
                            :class="
                                item.stock === 0 ? 'border-destructive' : ''
                            "
                            required
                        />
                        <InputError :message="errors.stock" />
                    </div>
                    <Button variant="outline" :disabled="processing">
                        Save
                    </Button>
                </Form>
            </div>
        </section>

        <section class="max-w-md">
            <Heading variant="small" title="Add food item" />

            <Form
                v-bind="FoodItemController.store.form()"
                reset-on-success
                :options="{ preserveScroll: true }"
                class="mt-4 space-y-4"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" name="name" placeholder="Adobo" required />
                    <InputError :message="errors.name" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="price">Price (₱)</Label>
                        <Input
                            id="price"
                            name="price"
                            type="number"
                            min="1"
                            required
                        />
                        <InputError :message="errors.price" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="stock">Stock</Label>
                        <Input
                            id="stock"
                            name="stock"
                            type="number"
                            min="0"
                            required
                        />
                        <InputError :message="errors.stock" />
                    </div>
                </div>
                <Button :disabled="processing">Add item</Button>
            </Form>
        </section>
    </div>
</template>
