-- =====================================================
-- InventaPro · Migration: translate role and status values to English
-- Safe to run on an existing database — only updates text values,
-- does not touch products, photos, sales or any other data.
-- =====================================================

USE inventapro;

-- Roles
UPDATE roles SET nombre_rol = 'Administrator'   WHERE nombre_rol = 'Administrador';
UPDATE roles SET nombre_rol = 'Warehouse Clerk' WHERE nombre_rol = 'Bodeguero';
UPDATE roles SET nombre_rol = 'Cashier'         WHERE nombre_rol = 'Cajero';

-- Payment method on existing sales
UPDATE salidas SET metodo_pago = 'cash' WHERE metodo_pago = 'efectivo';
UPDATE salidas SET metodo_pago = 'card' WHERE metodo_pago = 'tarjeta';

-- Supplier payment terms
UPDATE proveedores SET condicion_pago = 'Cash'   WHERE condicion_pago = 'Contado';
UPDATE proveedores SET condicion_pago = 'Credit' WHERE condicion_pago = 'Crédito';

-- Optional: rename the demo users' display names to match the new seed
UPDATE usuarios SET nombre_completo = 'System Administrator' WHERE usuario = 'admin';
UPDATE usuarios SET nombre_completo = 'Warehouse Clerk'       WHERE usuario = 'bodeguero';
UPDATE usuarios SET nombre_completo = 'Cashier on Duty'       WHERE usuario = 'cajero';
