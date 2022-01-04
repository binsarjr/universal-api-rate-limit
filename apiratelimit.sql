-- public.apiratelimit definition

-- Drop table

-- DROP TABLE public.apiratelimit;

CREATE TABLE public.apiratelimit (
	id serial4 NOT NULL,
	url varchar NULL,
	curl text NULL,
	ip varchar NULL,
	client_id varchar NULL,
	created_at timestamp NULL,
	CONSTRAINT apiratelimit_pk PRIMARY KEY (id)
);