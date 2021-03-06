"""Add corpora information

Revision ID: b8bec0892316
Revises: 1ec316af4a8f
Create Date: 2019-09-18 13:16:25.803351

"""
from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision = 'b8bec0892316'
down_revision = '1ec316af4a8f'
branch_labels = None
depends_on = None


def upgrade():
    modeEnum = sa.Enum('VAL', 'ADE', 'FLU', 'RAN', name='evalmode', create_type=True, schema='keopsdb')

    op.add_column('corpora', sa.Column('evalmode', modeEnum, nullable=False, server_default='VAL'), schema='keopsdb')

    # ### commands auto generated by Alembic - please adjust! ###
    pass
    # ### end Alembic commands ###


def downgrade():
    op.drop_column('corpora', 'evalmode', schema='keopsdb')

    # ### commands auto generated by Alembic - please adjust! ###
    pass
    # ### end Alembic commands ###
