-- =====================================================
-- InventaPro · Full database schema
-- (Field names kept in Spanish on purpose: they match the
-- original data dictionary from Fase 02-03 of the project.
-- Comments and seed data are in English.)
-- =====================================================

CREATE DATABASE IF NOT EXISTS inventapro
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE inventapro;

-- ---------------------------------------------------
-- roles
-- ---------------------------------------------------
CREATE TABLE roles (
    id_rol     INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(20) NOT NULL
);

-- ---------------------------------------------------
-- usuarios (system users)
-- ---------------------------------------------------
CREATE TABLE usuarios (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(80) NOT NULL,
    usuario         VARCHAR(30) NOT NULL UNIQUE,
    contrasena      VARCHAR(255) NOT NULL,
    id_rol          INT NOT NULL,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);

-- ---------------------------------------------------
-- categorias (product categories)
-- ---------------------------------------------------
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(50) NOT NULL
);

-- ---------------------------------------------------
-- unidades_medida (units of measure)
-- ---------------------------------------------------
CREATE TABLE unidades_medida (
    id_unidad   INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(30) NOT NULL,
    abreviatura VARCHAR(5)
);

-- ---------------------------------------------------
-- productos (products)
-- ---------------------------------------------------
CREATE TABLE productos (
    id_producto   INT AUTO_INCREMENT PRIMARY KEY,
    codigo_barra  VARCHAR(20),
    nombre        VARCHAR(80) NOT NULL,
    descripcion   VARCHAR(200),
    imagen        VARCHAR(255),
    id_categoria  INT,
    id_unidad     INT NOT NULL,
    precio_compra DECIMAL(8,2) NOT NULL,
    precio_venta  DECIMAL(8,2) NOT NULL,
    stock_actual  INT NOT NULL DEFAULT 0,
    stock_minimo  INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria),
    FOREIGN KEY (id_unidad) REFERENCES unidades_medida(id_unidad)
);

-- ---------------------------------------------------
-- proveedores (suppliers)
-- ---------------------------------------------------
CREATE TABLE proveedores (
    id_proveedor   INT AUTO_INCREMENT PRIMARY KEY,
    nombre         VARCHAR(80) NOT NULL,
    telefono       VARCHAR(15),
    direccion      VARCHAR(150),
    condicion_pago VARCHAR(20) NOT NULL
);

-- ---------------------------------------------------
-- producto_proveedor (product <-> supplier, many-to-many)
-- ---------------------------------------------------
CREATE TABLE producto_proveedor (
    id_producto   INT NOT NULL,
    id_proveedor  INT NOT NULL,
    precio_compra DECIMAL(8,2) NOT NULL,
    PRIMARY KEY (id_producto, id_proveedor),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor)
);

-- ---------------------------------------------------
-- entradas (stock-in headers)
-- ---------------------------------------------------
CREATE TABLE entradas (
    id_entrada   INT AUTO_INCREMENT PRIMARY KEY,
    fecha        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_proveedor INT NOT NULL,
    id_usuario   INT NOT NULL,
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- ---------------------------------------------------
-- detalle_entradas (stock-in line items)
-- ---------------------------------------------------
CREATE TABLE detalle_entradas (
    id_detalle    INT AUTO_INCREMENT PRIMARY KEY,
    id_entrada    INT NOT NULL,
    id_producto   INT NOT NULL,
    cantidad      INT NOT NULL,
    precio_compra DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (id_entrada) REFERENCES entradas(id_entrada) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- ---------------------------------------------------
-- clientes (customers)
-- ---------------------------------------------------
CREATE TABLE clientes (
    id_cliente      INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL UNIQUE,
    telefono        VARCHAR(20),
    compras_totales INT NOT NULL DEFAULT 0,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------
-- salidas (sale / stock-out headers)
-- ---------------------------------------------------
CREATE TABLE salidas (
    id_salida      INT AUTO_INCREMENT PRIMARY KEY,
    fecha          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario     INT NOT NULL,
    id_cliente     INT,
    cliente_nombre VARCHAR(100),
    metodo_pago    VARCHAR(20) NOT NULL DEFAULT 'cash',
    descuento      DECIMAL(8,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
);

-- ---------------------------------------------------
-- detalle_salidas (sale line items)
-- ---------------------------------------------------
CREATE TABLE detalle_salidas (
    id_detalle      INT AUTO_INCREMENT PRIMARY KEY,
    id_salida       INT NOT NULL,
    id_producto     INT NOT NULL,
    cantidad        INT NOT NULL,
    precio_venta    DECIMAL(8,2) NOT NULL,
    costo_unitario  DECIMAL(8,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (id_salida) REFERENCES salidas(id_salida) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- ---------------------------------------------------
-- auditoria (audit log)
-- ---------------------------------------------------
CREATE TABLE auditoria (
    id_log         INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario     INT NOT NULL,
    accion         VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(50),
    fecha_hora     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE INDEX idx_productos_stock ON productos(stock_actual);
CREATE INDEX idx_salidas_fecha ON salidas(fecha);
CREATE INDEX idx_entradas_fecha ON entradas(fecha);

-- =====================================================
-- SEED DATA
-- =====================================================

INSERT INTO roles (nombre_rol) VALUES
('Administrator'), ('Warehouse Clerk'), ('Cashier');

-- Passwords are already hashed with bcrypt (compatible with PHP's password_verify):
--   admin      / Admin2026!
--   bodeguero  / Bodega2026!
--   cajero     / Caja2026!
INSERT INTO usuarios (nombre_completo, usuario, contrasena, id_rol) VALUES
('System Administrator', 'admin',     '$2b$10$hu/YBa9xzu1QbiyEA5Bdbu3fLsAEdfUNZzgbnGGWDagN7RcyP6Xqe', 1),
('Warehouse Clerk',   'bodeguero', '$2b$10$7DhalCRrbdsFeDknZGwUEeUcsIZSHXayFJB2h2FFWwWdXD6ieaf1S', 2),
('Cashier on Duty',       'cajero',    '$2b$10$rivHu1UhNed9hQokJj/9vOt.hxz7/yShrNVWf9Fx/KjHqsml7BU7i', 3);

INSERT INTO categorias (nombre) VALUES
('Paint'), ('Fasteners'), ('Plumbing'), ('Tools'), ('Electrical');

INSERT INTO unidades_medida (nombre, abreviatura) VALUES
('Unit', 'un'), ('Box', 'bx'), ('Meter', 'm'), ('Gallon', 'gal'), ('Pound', 'lb');

INSERT INTO proveedores (nombre, telefono, direccion, condicion_pago) VALUES
('Constructor Supply Co.',    '2234-5678', 'Col. Escalon, San Salvador', 'Credit'),
('Central Hardware Inc.',     '2245-1122', 'Downtown, San Salvador',     'Cash'),
('Pipes & Fittings Ltd.',     '2278-9900', 'Soyapango, San Salvador',    'Credit');

INSERT INTO productos (codigo_barra, nombre, descripcion, id_categoria, id_unidad, precio_compra, precio_venta, stock_actual, stock_minimo) VALUES
('750100001', 'White enamel paint',     'Gallon, glossy finish',            1, 4, 8.50,  14.00, 2,  10),
('750100002', 'Blue latex paint',       'Gallon, indoor/outdoor',           1, 4, 7.00,  12.50, 25, 8),
('750100003', 'PVC pipe 1/2 inch',      '6-meter pipe',                     3, 3, 2.20,  4.00,  0,  15),
('750100004', 'Self-tapping screw 1"',  'Box of 100 units',                 2, 2, 3.10,  5.50,  40, 10),
('750100005', 'Electrical wire #12',    '100m roll, gauge 12',              5, 3, 45.00, 68.00, 6,  5),
('750100006', 'Claw hammer',            'Fiberglass handle',                4, 1, 6.00,  11.00, 14, 4),
('750100007', 'Measuring tape 5m',      'Reinforced case',                  4, 1, 3.20,  6.00,  18, 5),
('750100008', 'PVC elbow 1/2 inch',     'Pressure fitting, white',          3, 1, 0.30,  0.75,  60, 20);

-- =====================================================
-- NOTE: user passwords above are already hashed with bcrypt.
-- New users registered through the system get their hash
-- generated automatically by PHP's password_hash().
-- =====================================================
