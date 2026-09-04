USE startup_bms;

-- Client/business owner: full control of their own operations, shared guides are read-only.
UPDATE users SET permissions=JSON_OBJECT(
  'setup','view','plan','edit','notifications','edit','accounting','edit','payments','edit',
  'rewards','edit','content','view','users','edit','performance','view','support','edit'
) WHERE role='Client';

-- Client manager: manages daily operations but cannot change team permissions.
UPDATE users SET permissions=JSON_OBJECT(
  'setup','view','plan','edit','notifications','edit','accounting','edit','payments','edit',
  'rewards','edit','content','view','users','view','performance','view','support','edit'
) WHERE role='Manager';

-- Client staff: handles records and support, with no account administration.
UPDATE users SET permissions=JSON_OBJECT(
  'setup','view','plan','view','notifications','view','accounting','edit','payments','view',
  'rewards','view','content','view','users','none','performance','view','support','edit'
) WHERE role='Staff';

-- Viewer: read-only access to business information and no account administration.
UPDATE users SET permissions=JSON_OBJECT(
  'setup','view','plan','view','notifications','view','accounting','view','payments','view',
  'rewards','view','content','view','users','none','performance','view','support','view'
) WHERE role='Viewer';

-- Administrators are unrestricted by the permissions JSON; clear overrides for clarity.
UPDATE users SET permissions=JSON_OBJECT() WHERE role='Administrator';
