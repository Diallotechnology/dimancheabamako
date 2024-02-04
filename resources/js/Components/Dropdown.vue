<template>
    <li class="menu-item has-submenu">
        <a class="menu-link" href="">
            <slot name="nom"></slot>
        </a>
        <div class="submenu">
            <slot />
        </div>
    </li>
</template>
