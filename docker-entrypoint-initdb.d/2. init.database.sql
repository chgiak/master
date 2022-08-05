/*
 Navicat Premium Data Transfer

 Source Server         : marinetraffic
 Source Server Type    : PostgreSQL
 Source Server Version : 110001
 Source Host           : localhost:8766
 Source Catalog        : mtraffic_db
 Source Schema         : public

 Target Server Type    : PostgreSQL
 Target Server Version : 110001
 File Encoding         : 65001

 Date: 03/08/2022 22:47:24
*/


-- ----------------------------
-- Table structure for ship_positions
-- ----------------------------
DROP TABLE IF EXISTS "public"."ship_positions";

DROP SEQUENCE IF EXISTS "public"."ship_positions_id_seq";


CREATE SEQUENCE public.ship_positions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
	
	
CREATE TABLE "public"."ship_positions" (
  "id" int8 PRIMARY KEY DEFAULT nextval('ship_positions_id_seq'::regclass),
  "mmsi" int8 NOT NULL,
  "status" int2 NOT NULL,
  "stationid" int2 NOT NULL,
  "speed" int2 NOT NULL,
  "position" point NOT NULL,
  "course" int2 NOT NULL,
  "heading" int2 NOT NULL,
  "rotation" varchar(3),
  "timestamp" timestamptz(6) NOT NULL DEFAULT now(),
  "created_on" timestamptz(6) NOT NULL DEFAULT now()
)
;

-- ----------------------------
-- Indexes structure for table ship_positions
-- ----------------------------
CREATE UNIQUE INDEX "ship_positions_id_idx" ON "public"."ship_positions" USING btree (
  "id" "pg_catalog"."int8_ops" ASC NULLS LAST
);
CREATE INDEX "ship_positions_mmsi_idx" ON "public"."ship_positions" USING btree (
  "mmsi" "pg_catalog"."int8_ops" ASC NULLS LAST
);
CREATE INDEX "ship_positions_position_idx" ON "public"."ship_positions" USING gist (
  "position" "pg_catalog"."point_ops"
);
CREATE INDEX "ship_positions_timestamp_idx" ON "public"."ship_positions" USING btree (
  "timestamp" "pg_catalog"."timestamptz_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table ship_positions
-- ----------------------------
--ALTER TABLE "public"."ship_positions" ADD CONSTRAINT "ship_positions_pkey" PRIMARY KEY ("id");
